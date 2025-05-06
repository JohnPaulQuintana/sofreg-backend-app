<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmployeeAttendance extends Controller
{
    /**
     * Get the latest attendance record for each employee
     * and add the total records per month per employee
     */
    public function employeeAttendancePerMonth()
    {
        // $perPage = $request->get('per_page', 1);

        $attendance = Attendance::with('user') // Updated to use 'user' relation
            ->select('attendances.*')
            ->join(\DB::raw('(SELECT MAX(id) as latest_id FROM attendances GROUP BY employee_id) as latest'), 'attendances.id', '=', 'latest.latest_id')
            ->orderBy('attendances.created_at', 'desc')
            ->paginate(10)
            ->through(function ($record) {
                $currentMonthCount = Attendance::where('employee_id', $record->employee_id)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count();

                $record->current_month_count = $currentMonthCount;
                return $record;
            });

        $attendance_today = Attendance::with('user')
            ->select('attendances.*')
            ->join(\DB::raw('(SELECT MAX(id) as latest_id FROM attendances GROUP BY employee_id) as latest'), 'attendances.id', '=', 'latest.latest_id')
            ->whereDate('attendances.created_at', now()->toDateString()) // Filter for today only
            ->orderBy('attendances.created_at', 'desc')
            ->paginate(10);


        return response()->json(['employee_attendance' => $attendance, 'employee_attendance_today' => $attendance_today]);
    }
    /**
     * Get all users with role 'employee' and their attendance records
     * for the current month used for exporting to excell
     */
    public function exportAttendance()
    {
        // $users = User::with(['attendance' => function($query) {
        //     $query->whereMonth('created_at', now()->month)
        //           ->whereYear('created_at', now()->year)
        //           ->orderBy('created_at', 'desc');
        // }])->where('role', 'employee')->get();

        $attendance = Attendance::with('user')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['employee_attendance' => $attendance]);
    }

    public function getAttendanceSummary()
    {
        $totalEmployees = Attendance::distinct('employee_id')->count('employee_id');

        // Explanation
        // Get all employees (User::where('role', 'employee')) with employee_id and time_of_duty_start.
        // → Ensures we only check employees whose shift has already started, preventing false absences.

        // Find employees who attended today (Attendance::whereDate('date', $today)).

        // Compare lists to determine who is absent (whereNotIn).
        $today = Carbon::today();
        // ✅ Step 1: Get all employees and their time of duty
        $employees = User::where('role', 'employee')
        ->whereTime('time_of_duty_start', '<=', now()->format('H:i:s')) // ✅ Only employees whose shift started
        ->select('id as user_id', 'employee_id', 'time_of_duty_start')
        ->get();

        // ✅ Step 2: Get attendance records for today
        $attendedEmployees = Attendance::whereDate('date', $today)
        ->whereIn('employee_id', $employees->pluck('employee_id')) // Only check relevant employees
        ->pluck('employee_id')
        ->toArray();

        // ✅ Step 3: Find employees who are absent
        $absentEmployees = $employees->whereNotIn('employee_id', $attendedEmployees);

        $absentCount = $absentEmployees->count();


        // $absentEmployees = Attendance::whereDate('date', today())
        //     ->distinct('employee_id')
        //     ->count('employee_id');

        $mostLateEmployee = Attendance::where('status', 'Late')
        ->whereMonth('date', Carbon::now()->month)
        ->whereYear('date', Carbon::now()->year)
        ->selectRaw('employee_id, COUNT(*) as late_count')
        ->groupBy('employee_id')
        ->orderByDesc('late_count')
        ->first();

        $noAbsentRecord = Attendance::where('status', '!=', 'Absent')
            ->distinct('employee_id')
            ->count('employee_id');

        // $absentToday = User::where('role','employee')->get();
        return response()->json([
            'total_employees' => $totalEmployees,
            'absent_employees' => $absentCount,
            'most_late_employee' => $mostLateEmployee ? $mostLateEmployee->employee_id : null,
            'no_absent_record' => $noAbsentRecord
        ]);
    }
}
