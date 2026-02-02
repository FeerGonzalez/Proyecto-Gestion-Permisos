<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadosPermisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estados = [
            'pendiente',
            'aprobado',
            'rechazado',
            'cancelado',
        ];

        foreach ($estados as $estado) {
            DB::table('estados_permiso')->updateOrInsert(
                ['nombre' => $estado],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
