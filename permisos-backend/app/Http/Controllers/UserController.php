<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return User::withTrashed()
            ->orderBy('name')
            ->get();
    }

    public function show(User $user)
    {
        return $user;
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

        return response()->json($user, 201);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:empleado,supervisor,rrhh',
        ]);

        $user->update($request->only('name', 'email', 'role'));

        return response()->json([
            'message' => 'Usuario actualizado',
            'user' => $user
        ]);
    }

    public function horasDisponibles()
    {
        $user = auth()->user(); // usuario logueado

        return response()->json([
            'horas_disponibles' => $user->horas_disponibles,
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

        return response()->json(['message' => 'Usuario desactivado']);
    }

    public function activar($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return response()->json(['message' => 'Usuario activado']);
    }
}
