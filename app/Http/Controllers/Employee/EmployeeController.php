<?php

namespace App\Http\Controllers\Employee;

use App\Events\AttendanceUpdated;
use App\Http\Controllers\Controller;
use App\Models\Attendance\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    //insert attendance
    public function clockInAttendance(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'time_in' => 'required',
            'time_out' => 'nullable',
            'status' => 'required',
            'note' => 'nullable',
            'captured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('captured_image')) {
            $imagePath = $request->file('captured_image')->store('attendance_images', 'public');
        }

        $attendance = Attendance::create([
            'employee_id' => auth()->user()->employee_id,
            'date' => $request->date,
            'clock_in' => $request->time_in,
            'clock_out' => $request->time_out,
            'status' => $request->status,
            'note' => "Present Employee",
            'captured_image' => $imagePath,
        ]);

        event(new AttendanceUpdated($attendance));
        
        return response()->json(['message' => 'Attendance created successfully', 'attendance' => $attendance]);
    }
    // Clock out
    public function clockOutAttendance(Request $request)
    {
        // it has  date on request skip for now
        $request->validate([
            'time_out' => 'required|date_format:H:i:s', // Assuming time format
        ]);

        $employeeId = auth()->user()->employee_id;
        $today = now()->toDateString();

        $attendance = Attendance::where('employee_id', $employeeId)
            ->whereDate('created_at', $today)
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'Attendance record not found'], 404);
        }

        $attendance->update([
            'clock_out' => $request->time_out,
        ]);

        event(new AttendanceUpdated($attendance));

        return response()->json(['message' => 'Clocked out successfully', 'attendance' => $attendance]);
    }


    public function getAttendanceByWeek(){
        // use Carbon\Carbon;
        
        // return a list of attendance records for the current week
        $attendance = Attendance::where('employee_id', auth()->user()->employee_id)
            ->whereDate('date', '>=', Carbon::now()->startOfWeek()->format('Y-m-d'))
            ->whereDate('date', '<=', Carbon::now()->endOfWeek()->format('Y-m-d'))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json(['attendance' => $attendance]);
    }
}
