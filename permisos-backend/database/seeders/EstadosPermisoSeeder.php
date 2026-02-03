<?php

namespace Database\Seeders;

use App\Models\EstadoPermiso;
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
            EstadoPermiso::updateOrCreate(
                ['nombre' => $estado],
                []
            );
        }
    }
}
