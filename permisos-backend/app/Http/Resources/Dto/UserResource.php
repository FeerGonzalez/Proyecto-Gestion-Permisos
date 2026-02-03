<?php

namespace App\Http\Resources\Dto;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'horas_disponibles' => $this->horas_disponibles,
            'deleted_at' => $this->deleted_at,

            // Relaciones (solo si se cargan)
            'permisos' => $this->whenLoaded('permisos'),
            'permisos_aprobados' => $this->whenLoaded('permisosAprobados'),
        ];
    }
}
