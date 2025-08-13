<?php

namespace App\Http\Controllers\API\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\CoordinatorAccountCreatedMail;

class CoordinatorController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'emailOrUsername' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = $request->input('emailOrUsername');
        $password = $request->input('password');

        $user = User::with('sector') // Eager load sector
            ->where(function ($query) use ($loginInput) {
                $query->where('email', $loginInput)
                      ->orWhere('username', $loginInput);
            })->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'message' => 'Invalid email/username or password.'
            ], 401);
        }

        if ($user->role !== 'coordinator') {
            return response()->json([
                'message' => 'Access denied. Only coordinators can log in here.'
            ], 403);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Account is inactive.'
            ], 403);
        }

        $token = $user->createToken('Coordinator Token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'data' => $user, // includes sector now
            'token' => $token,
            'token_type' => 'Bearer'
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'mname' => 'nullable|string|max:255',
            'lname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'sector_id' => 'required|exists:sector,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rawPassword = $request->password;

            $user = User::create([
                'fname' => $request->fname,
                'mname' => $request->mname,
                'lname' => $request->lname,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($rawPassword),
                'role' => 'coordinator',
                'sector_id' => $request->sector_id,
                'status' => 'active',
            ]);

            Mail::to($user->email)->send(new CoordinatorAccountCreatedMail($user, $rawPassword));

            $token = $user->createToken('Coordinator Token')->plainTextToken;

            return response()->json([
                'message' => 'Coordinator created successfully.',
                'data' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating coordinator',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $coordinators = User::whereRaw('LOWER(role) = ?', ['coordinator'])
                ->with('sector')
                ->get();

            return response()->json([
                'message' => 'List of coordinators retrieved successfully.',
                'data' => $coordinators,
                'token' => $request->bearerToken(),
                'token_type' => 'Bearer'
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Coordinator fetch error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to retrieve coordinators.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $coordinator = User::where('role', 'coordinator')->with('sector')->find($id);

        if (!$coordinator) {
            return response()->json([
                'message' => 'Coordinator not found.'
            ], 404);
        }

        return response()->json([
            'message' => 'Coordinator retrieved successfully.',
            'data' => $coordinator,
            'token' => $request->bearerToken(),
            'token_type' => 'Bearer'
        ], 200);
    }
}
