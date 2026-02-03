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

    public static function pendiente(): self
    {
        return static::where('nombre', self::PENDIENTE)->firstOrFail();
    }

    public static function aprobado(): self
    {
        return static::where('nombre', self::APROBADO)->firstOrFail();
    }

    public static function rechazado(): self
    {
        return static::where('nombre', self::RECHAZADO)->firstOrFail();
    }

    public static function cancelado(): self
    {
        return static::where('nombre', self::CANCELADO)->firstOrFail();
    }
}
