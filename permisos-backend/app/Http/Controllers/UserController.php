<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\Dto\UserResource;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withTrashed()
            ->orderBy('name')
            ->paginate(10);

        return UserResource::collection($users);
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function store(StoreUserRequest $request)
    {
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

    public function update(UpdateUserRequest $request, User $user)
    {
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
