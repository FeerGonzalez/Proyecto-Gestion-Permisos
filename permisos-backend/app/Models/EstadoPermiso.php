<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoPermiso extends Model
{
    protected $table = 'estados_permiso';

    protected $fillable = ['nombre'];

    public const PENDIENTE  = 'pendiente';
    public const APROBADO   = 'aprobado';
    public const RECHAZADO  = 'rechazado';
    public const CANCELADO  = 'cancelado';
}
