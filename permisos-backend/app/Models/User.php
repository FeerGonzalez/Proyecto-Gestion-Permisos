<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    //Roles
    public const ROLE_EMPLEADO   = 'empleado';
    public const ROLE_SUPERVISOR = 'supervisor';
    public const ROLE_RRHH       = 'rrhh';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts de atributos
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    //Relaciones

    // Permisos solicitados por el usuario (empleado)
    public function permisos()
    {
        return $this->hasMany(Permiso::class);
    }

    // Permisos aprobados/rechazados por el usuario (supervisor / rrhh)
    public function permisosAprobados()
    {
        return $this->hasMany(Permiso::class, 'aprobado_por');
    }

    //Helpers
    public function isEmpleado(): bool
    {
        return $this->role === self::ROLE_EMPLEADO;
    }

    public function isSupervisor(): bool
    {
        return $this->role === self::ROLE_SUPERVISOR;
    }

    public function isRRHH(): bool
    {
        return $this->role === self::ROLE_RRHH;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }
}
