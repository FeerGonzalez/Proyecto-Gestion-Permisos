<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $fillable = [
        'user_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'horas_totales',
        'motivo',
        'estado',
        'estado_id',
        'examinado_por',
        'examinado_en',
    ];

    //Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function examinadoPor()
    {
        return $this->belongsTo(User::class, 'examinado_por');
    }

    public function estadoRel()
    {
        return $this->belongsTo(EstadoPermiso::class, 'estado_id');
    }

    //Scope
    public function scopePendientes($query)
    {
        return $query->whereHas('estadoRel', function ($q) {
            $q->where('nombre', EstadoPermiso::PENDIENTE);
        });
    }

    //Helpers
    public function esPendiente(): bool
    {
        return $this->estado === EstadoPermiso::PENDIENTE;
    }

    public function setEstado(string $nombreEstado): void
    {
        $estado = EstadoPermiso::where('nombre', $nombreEstado)->firstOrFail();

        $this->estado = $nombreEstado; // FE
        $this->estado_id = $estado->id; // DB normalizada
    }

    public function puedeSerAprobadoPor(User $user): bool
    {
        return $this->usuario_id !== $user->id;
    }

}
