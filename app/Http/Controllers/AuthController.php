<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\HttpResponses;
use App\Traits\Logging;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    use HttpResponses, Logging;

    public function register(Request $request) {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password'])
        ]);

        $token = $user->createToken(env('TOKEN1', 'token-') . $user->email)->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return $this->success($response, 'Registered Successfilly', 201);
    }

    public function login(Request $request) {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        // Check email
        $user = User::with('role')
            ->where('email', $fields['email'])
            ->orWhere('username', $fields['email'])
            ->first();

        // Check password
        if(!$user || $user->password == null || !Hash::check($fields['password'], $user->password)) {
            return $this->error(null, 'ชื่อผู้ใช้งานไม่ถูกต้อง', 401);
        }

        $token = $user->createToken(env('TOKEN1', 'token-') . $user->email)->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        Log::info('User logged in', $this->getActorData($user->id));

        return $this->success($response, 'Login successfully', 201);
    }

    public function logout(Request $request) {
        $user = $request->user();
        $request->user()->tokens()->delete();

        Log::info('User logged out', $this->getActorData($user->id));

        return $this->success(null, 'Logged out successfully');
    }
}
