<?php

namespace App\Http\Controllers\Api\Beneficiary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class BeneficiaryAuthController extends Controller
{
    /**
     * Register a new beneficiary and return auth token
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fname'           => 'required|string|max:50',
            'mname'           => 'nullable|string|max:50',
            'lname'           => 'required|string|max:50',
            'extension_name'  => 'nullable|string|max:10',
            'username'        => 'required|string|max:50|unique:users,username',
            'email'           => 'nullable|email|unique:users,email',
            'phone_number'    => 'required|string|max:20|regex:/^\+?[0-9]{10,15}$/',
            'password'        => 'required|string|min:8|confirmed',
        ], [
            'phone_number.regex' => 'Phone number must be between 10 and 15 digits, and can start with +.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'fname'           => $request->fname,
            'mname'           => $request->mname,
            'lname'           => $request->lname,
            'extension_name'  => $request->extension_name,
            'username'        => $request->username,
            'email'           => $request->email,
            'phone_number'    => $request->phone_number,
            'role'            => 'beneficiary',
            'status'          => 'active',
            'sector_id'       => null,
            'password'        => Hash::make($request->password),
        ]);

        // Create Sanctum token for the newly registered user
        $token = $user->createToken('BeneficiaryToken')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Beneficiary registered successfully',
            'data'    => $user,
            'token'   => $token
        ], 201);
    }


  public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'emailOrUsername' => 'required|string',
        'password'        => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    // Find user by username, phone, or email
    $user = User::where('username', $request->emailOrUsername)
                ->orWhere('phone_number', $request->emailOrUsername)
                ->orWhere('email', $request->emailOrUsername)
                ->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid login credentials'
        ], 401);
    }

    // Generate token
    $token = $user->createToken('BeneficiaryToken')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Login successful',
        'data'    => $user,
        'token'   => $token
    ]);
}



    /**
     * Check if username or email is already taken
     */
    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'nullable|string|max:50',
            'email'    => 'nullable|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $response = [
            'success'         => true,
            'username_checked'=> false,
            'email_checked'   => false,
        ];

        // Check username if provided
        if ($request->filled('username')) {
            $exists = User::where('username', $request->username)->exists();
            $response['username_checked'] = true;
            $response['username_available'] = !$exists;
            $response['username_message'] = $exists
                ? 'Username is already taken.'
                : 'Username is available.';
        }

        // Check email if provided
        if ($request->filled('email')) {
            $exists = User::where('email', $request->email)->exists();
            $response['email_checked'] = true;
            $response['email_available'] = !$exists;
            $response['email_message'] = $exists
                ? 'Email is already taken.'
                : 'Email is available.';
        }

        return response()->json($response);

    }
    
    public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
}

}

