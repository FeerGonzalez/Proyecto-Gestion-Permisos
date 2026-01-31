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
        ]);

        //Usuario con Rol de supervisor
        User::create([
            'name' => 'Supervisor Demo',
            'email' => 'supervisor@demo.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPERVISOR,
        ]);

        //Usuario con Rol de RRHH
        User::create([
            'name' => 'RRHH Demo',
            'email' => 'rrhh@demo.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_RRHH,
        ]);
    }
}
