<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        $empleado = User::where('role', User::ROLE_EMPLEADO)->first();
        $supervisor = User::where('role', User::ROLE_SUPERVISOR)->first();

        // Permiso pendiente
        Permiso::create([
            'user_id' => $empleado->id,
            'fecha' => now()->toDateString(),
            'hora_inicio' => '08:00',
            'hora_fin' => '09:30',
            'horas_totales' => 1.5,
            'motivo' => 'Trámite personal',
            'estado' => 'pendiente',
        ]);

        // Permiso aprobado
        Permiso::create([
            'user_id' => $empleado->id,
            'fecha' => now()->subDays(5)->toDateString(),
            'hora_inicio' => '10:00',
            'hora_fin' => '11:00',
            'horas_totales' => 1.0,
            'motivo' => 'Consulta médica',
            'estado' => 'aprobado',
            'examinado_por' => $supervisor->id,
            'examinado_en' => Carbon::now()->subDays(4),
        ]);

        // Permiso rechazado
        Permiso::create([
            'user_id' => $empleado->id,
            'fecha' => now()->subDays(10)->toDateString(),
            'hora_inicio' => '12:00',
            'hora_fin' => '13:00',
            'horas_totales' => 1.0,
            'motivo' => 'Asunto personal',
            'estado' => 'rechazado',
            'examinado_por' => $supervisor->id,
            'examinado_en' => Carbon::now()->subDays(9),
        ]);
    }
}
