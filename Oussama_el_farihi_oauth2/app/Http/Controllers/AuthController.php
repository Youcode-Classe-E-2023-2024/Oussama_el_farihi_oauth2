<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $token = $user->createToken('MyAppToken')->accessToken;

        return response(['user' => $user, 'access_token' => $token]);
    }

    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!Auth::attempt($loginData)) {
            return response(['message' => 'Invalid credentials']);
        }

        $user = Auth::user();
        $accessToken = $user->createToken('authToken')->plainTextToken;

        return response(['user' => $user, 'access_token' => $accessToken]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response(['message' => 'You have been successfully logged out!']);
    }
}
