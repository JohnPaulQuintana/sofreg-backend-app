<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance\Attendance;
use App\Models\User;
use Illuminate\Http\Request;

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


        return response()->json(['employee_attendance'=>$attendance]);
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
        return response()->json(['employee_attendance'=>$attendance]);
    }

}
