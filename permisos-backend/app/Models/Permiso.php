<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Permiso extends Model
{
    protected $fillable = [
        'user_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'horas_totales',
        'motivo',
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

    // Query Scopes
    public function scopePendientes(Builder $query): Builder
    {
        return $query->whereHas('estadoRel', function ($q) {
            $q->where('nombre', EstadoPermiso::PENDIENTE);
        });
    }

    public function scopeGestionados(Builder $query): Builder
    {
        return $query->whereHas('estadoRel', function ($q) {
            $q->whereIn('nombre', [
                EstadoPermiso::APROBADO,
                EstadoPermiso::RECHAZADO,
            ]);
        });
    }

    public function scopeDelUsuario(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeExaminadoPorUsuario(Builder $query, int $userId): Builder
    {
        return $query->where('examinado_por', $userId);
    }

    //Helpers
    public function esPendiente(): bool
    {
        return $this->estadoRel?->nombre === EstadoPermiso::PENDIENTE;
    }

    public function setEstado(string $nombreEstado): void
    {
        $estado = EstadoPermiso::where('nombre', $nombreEstado)->firstOrFail();
        $this->estado_id = $estado->id;
    }

    public function puedeSerAprobadoPor(User $user): bool
    {
        return $this->user_id !== $user->id;
    }

}

