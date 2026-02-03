<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\Dto\UserResource;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withTrashed()
            ->orderBy('name')
            ->get();

        return UserResource::collection($users);
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|in:empleado,supervisor,rrhh',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:empleado,supervisor,rrhh',
        ]);

        $user->update($request->only('name', 'email', 'role'));

        return (new UserResource($user))->additional([
            'message' => 'Usuario actualizado',
        ]);
    }

    public function horasDisponibles()
    {
        return response()->json([
            'horas_disponibles' => auth()->user()->horas_disponibles,
        ]);
    }

    public function desactivar(User $user)
    {
        if ($user->id === Auth::id()) {
            return response()->json([
                'error' => 'No podés desactivar tu propio usuario'
            ], 422);
        }

        $user->delete();

        return (new UserResource($user))->additional([
            'message' => 'Usuario desactivado'
        ]);
    }

    public function activar($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return (new UserResource($user))->additional([
            'message' => 'Usuario activado'
        ]);
    }
}
