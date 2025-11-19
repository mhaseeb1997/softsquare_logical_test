<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return response()->json([
            'user' => $user->only(['id', 'name', 'email']),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $user = User::where('email', $credentials['email'])->first();
        if ($user === null || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.'
            ], 401);
        }
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json([
            'user' => $user->only(['id', 'name', 'email']),
            'token' => $token,
        ], 200);
    }

    public function logout(): JsonResponse
    {
        $user = auth()->user();
        if ($user !== null) {
            $user->tokens()->delete();
        }
        return response()->json(['message' => 'Logged out'], 200);
    }
}
