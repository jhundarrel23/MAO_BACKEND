<?php

namespace App\Http\Controllers\Api\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AdminSanctumController extends Controller
{
    /**
     * Handle admin login and return Sanctum token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'emailOrUsername' => ['required'],
            'password' => ['required'],
        ]);

        $user = User::where(function ($query) use ($request) {
            $query->where('email', $request->emailOrUsername)
                  ->orWhere('username', $request->emailOrUsername);
        })->where('role', 'admin')->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials or not an admin.'
            ], 401);
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'message' => 'Admin login successful.',
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'fname' => $user->fname,
                'lname' => $user->lname,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);
    }

    /**
     * Handle admin registration.
     */
  public function register(Request $request)
{
    $request->validate([
        'username' => 'required|string|unique:users,username',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|confirmed|min:6',
    ]);

    $user = User::create([
        'username' => $request->username,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'role' => 'admin',
        'status' => 'active',
        'fname' => 'Demo', // Add default fname/lname if required
        'lname' => 'Admin',
    ]);

    return response()->json([
        'message' => 'Admin registered successfully!',
        'user' => $user,
    ]);
}



    /**
     * Logout and revoke all tokens for the user.
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully.'
        ]);
    }
}
