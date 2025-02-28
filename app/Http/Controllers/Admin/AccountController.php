<?php

namespace App\Http\Controllers\Admin;

use App\Events\AttendanceUpdated;
use App\Events\EmployeeCreated;
use App\Http\Controllers\Controller;
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
            'password' => Hash::make('softreg123'),
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

    //get all employee account
    public function getAllEmployeeAccount()
    {
        $users = User::where('role', 'employee')->orderBy('created_at', 'desc')->paginate(10);
        
        return response()->json(['users' => $users]);
    }
}
