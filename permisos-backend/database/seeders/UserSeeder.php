<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        //Usuario con Rol de empleado
        User::create([
            'name' => 'Empleado Demo',
            'email' => 'empleado@demo.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_EMPLEADO,
            'horas_disponibles' => 9,
        ]);

        //Usuario con Rol de supervisor
        User::create([
            'name' => 'Supervisor Demo',
            'email' => 'supervisor@demo.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPERVISOR,
            'horas_disponibles' => 10,
        ]);

        //Usuario con Rol de RRHH
        User::create([
            'name' => 'RRHH Demo',
            'email' => 'rrhh@demo.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RRHH,
            'horas_disponibles' => 10,
        ]);
    }
}
