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
        'aprobado_por',
        'aprobado_en',
    ];

    public const PENDIENTE = 'pendiente';
    public const APROBADO  = 'aprobado';
    public const RECHAZADO = 'rechazado';

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function aprobadoPor()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', self::PENDIENTE);
    }

}
