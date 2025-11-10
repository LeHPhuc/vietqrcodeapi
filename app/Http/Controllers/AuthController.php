<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\RegisterAuthRequest;
use App\Http\Requests\LoginAuthRequest;

class AuthController extends Controller
{
    public function register(RegisterAuthRequest $request)
    {
        $data = $request->validated();

        $user = User::create($data);

        // Tạo Personal Access Token
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], Response::HTTP_CREATED);
    }


    // Đăng nhập, xoá token cũ rồi cấp token mới
    public function login(LoginAuthRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Email hoặc mật khẩu không đúng'], 422);
        }

        //xoá token cũ
        $user->tokens()->delete();

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    // Đăng xuất: thu hồi token đang dùng
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Đã đăng xuất']);
    }
}
