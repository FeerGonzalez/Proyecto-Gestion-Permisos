<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Http\Resources\Dto\UserResource;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest  $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => User::ROLE_EMPLEADO,
        ]);

        $token = $user->createToken('angular-token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'token' => $token,
            'user' => new UserResource($user),
        ], 201);
    }

    public function login(LoginRequest  $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $token = $user->createToken('angular-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada'
        ]);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function updateProfile(UpdateProfileRequest  $request)
    {
        $user = $request->user();

        $user->update([
            'email' => $request->email,
        ]);

        return response()->json([
            'message' => 'Perfil actualizado',
            'user' => new UserResource($user),
        ]);
    }

    public function changePassword(ChangePasswordRequest  $request)
    {
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return response()->json(['error' => 'Contraseña actual incorrecta'], 422);
        }

        Auth::user()->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'Contraseña actualizada']);
    }
}

