<?php

namespace Database\Factories;

use App\Models\Permiso;
use App\Models\User;
use App\Models\EstadoPermiso;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermisoFactory extends Factory
{
    protected $model = Permiso::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'fecha' => $this->faker->date(),
            'hora_inicio' => '08:00',
            'hora_fin' => '10:00',
            'horas_totales' => 2,
            'motivo' => $this->faker->sentence(),
            'estado_id' => EstadoPermiso::pendiente()->id,
            'examinado_por' => null,
            'examinado_en' => null,
        ];
    }

    public function pendiente(): self
    {
        return $this->state(function () {
            return [
                'estado_id' => EstadoPermiso::where('nombre', EstadoPermiso::PENDIENTE)->first()?->id
            ];
        });
    }
}
