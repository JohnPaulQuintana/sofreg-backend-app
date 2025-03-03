<?php

namespace App\Http\Controllers\Admin;

use App\Events\AttendanceUpdated;
use App\Events\EmployeeCreated;
use App\Http\Controllers\Controller;
use App\Models\Attendance\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    //add employee account
    public function addEmployeeAccount(Request $request)
    {

        $request->validate([
            // 'employeeId' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contactNo' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'position' => 'required|string|max:255',  
            'timeOfDutyStart' => 'required|date_format:H:i',
            'timeOfDutyEnd' => 'required|date_format:H:i'
        ]);

        // validation passed
        $employeeId = 'SR-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $users = User::create([
            'employee_id' => $employeeId,
            'name' => $request->name,
            'email' => $request->email ?? 'employee' . rand(1000, 9999) . '@company.com',
            'password' => Hash::make('sofreg1234!'),
            'role' => 'employee',
            'address' => $request->address,
            'contact_no' => $request->contactNo,
            'department' => $request->department,
            'position' => $request->position,
            'time_of_duty_start' => $request->timeOfDutyStart,
            'time_of_duty_end' => $request->timeOfDutyEnd,
        ]);

        event(new EmployeeCreated($users));

        return response()->json(['user' => $users, 'message' => 'Employee account created successfully']);
    }

    //edit employee
    public function editEmployee(Request $request){

        $request->validate([
            'employeeId' => 'required|string|max:255|exists:users,employee_id',
        ]);

        // Proceed with editing logic if validation passes
        $employee = User::where('employee_id',$request->employeeId)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }
    
        // Your update logic here
        return response()->json(['message' => 'Employee found!', 'employee' => $employee]);

    }

    public function updateEmployee(Request $request){

        $validatedData = $request->validate([
            'employee_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contact_no' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'position' => 'required|string|max:255',  
            'time_of_duty_start' => 'required',
            'time_of_duty_end' => 'required'
        ]);

        try {
            $employee = User::where('employee_id', $validatedData['employee_id'])->firstOrFail();
    
            $employee->update($validatedData);
            
            event(new EmployeeCreated($employee));

            return response()->json(['message' => 'Employee updated successfully', 'employee' => $employee], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Employee not found'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'An error occurred while updating the employee'], 500);
        }

    }

    // delete employee
    public function deleteEmployee(Request $request)
    {
        $employeeId = $request->input('employee_id');

        $user = User::where('employee_id', $employeeId)->first();

        if (!$user) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $deletedUserData = $user->toArray(); // Save user data before deletion
        $user->delete();

        event(new EmployeeCreated($deletedUserData)); // Now pass the saved data
        return response()->json(['message' => 'Employee deleted successfully']);
    }

    // view employee attendance record
    public function viewEmployeeAttendance(Request $request){
        $employeeId = $request->input('employee_id');

        $employeeRecords = Attendance::where('employee_id',$employeeId)->orderBy('date', 'desc')->get();
        if (!$employeeRecords) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        return response()->json(['message' => 'Employee attendance records', 'attendance_records' => $employeeRecords]);
    }

    //get all employee account
    public function getAllEmployeeAccount()
    {
        $users = User::where('role', 'employee')->orderBy('created_at', 'desc')->paginate(10);
        
        return response()->json(['users' => $users]);
    }
}
