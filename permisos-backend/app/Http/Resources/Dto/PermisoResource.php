<?php

namespace App\Http\Resources\Dto;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermisoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fecha' => $this->fecha,
            'hora_inicio' => $this->hora_inicio,
            'hora_fin' => $this->hora_fin,
            'horas_totales' => $this->horas_totales,
            'motivo' => $this->motivo,

            // Estado EXPUESTO como string
            'estado' => $this->estadoRel?->nombre,

            // Auditoría
            'examinado_por' => $this->examinado_por,
            'examinado_en' => $this->examinado_en,

            // Usuario
            'user_id' => $this->user_id,
            'usuario' => $this->whenLoaded('usuario'),

            // Relaciones opcionales
            'examinador' => $this->whenLoaded('examinadoPor'),
        ];
    }
}
