<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function test()
    {
        return response()->json(["status"=>200, "message"=>"API is working now."]);
    }
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,employee'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('employee_id', $request->employee_id)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token, 'role' => $user->role, 'user' => $user]);
    }

    public function logout(Request $request)
    {
        try {
            // Revoke the current user's token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Successfully logged out'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to log out',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        Log::info('Received request:', $request->all()); // Log request data

        // ✅ Validate request data
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_password' => 'required|string|min:6',
        ]);

        // ✅ Find user
        $user = User::find($validatedData['user_id']);
        if (!$user) {
            Log::error('User not found:', ['user_id' => $validatedData['user_id']]);
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        // ✅ Update password
        $user->password = Hash::make($validatedData['new_password']);
        $user->password_changed = true;
        $user->save();

        Log::info('Password updated successfully:', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    }

    public function resetPassword(Request $request)
    {
        Log::info('Received request:', $request->all()); // Log request data

        // ✅ Validate request data
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:users,employee_id',
        ]);

        // ✅ Find user
        $user = User::where('employee_id',$validatedData['employee_id'])->first();
        if (!$user) {
            Log::error('User not found:', ['user_id' => $validatedData['user_id']]);
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        // ✅ Update password
        $user->password = Hash::make('sofreg1234!');
        $user->password_changed = false;
        $user->save();

        Log::info('Password updated successfully:', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    }
}
