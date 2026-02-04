<?php

namespace App\Services;

use App\Models\EstadoPermiso;
use App\Models\Permiso;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PermisoService
{
    /**
     * Creates a new permission request.
     *
     * @throws \DomainException if outside working hours or insufficient hours
     */
    public function crearPermiso(User $user, array $data): Permiso
    {
        $inicio = Carbon::createFromFormat('H:i', $data['hora_inicio']);
        $fin = Carbon::createFromFormat('H:i', $data['hora_fin']);
        $horas = $inicio->floatDiffInHours($fin);

        if (!$this->validarHorarioLaboral($inicio, $fin)) {
            throw new \DomainException('El permiso debe estar dentro del horario laboral (07:30 a 13:30)');
        }

        if (!$user->tieneHorasSuficientes($horas)) {
            throw new \DomainException(
                "No tenés horas suficientes. Disponibles: {$user->horas_disponibles}, Solicitadas: {$horas}"
            );
        }

        $permiso = Permiso::create([
            'user_id' => $user->id,
            'fecha' => $data['fecha'],
            'hora_inicio' => $data['hora_inicio'],
            'hora_fin' => $data['hora_fin'],
            'horas_totales' => $horas,
            'motivo' => $data['motivo'],
            'estado_id' => EstadoPermiso::pendiente()->id,
        ]);

        return $permiso->load('usuario', 'estadoRel');
    }

    /**
     * Updates an existing pending permission.
     *
     * @throws \DomainException if outside working hours or insufficient hours
     * @throws \InvalidArgumentException if permission is not pending or not owned
     */
    public function actualizarPermiso(Permiso $permiso, User $user, array $data): Permiso
    {
        if ($permiso->user_id !== $user->id) {
            throw new \InvalidArgumentException('Solo puedes modificar tus propios permisos');
        }

        if (!$permiso->esPendiente()) {
            throw new \InvalidArgumentException('Solo se pueden modificar permisos pendientes');
        }

        $inicio = Carbon::createFromFormat('H:i', $data['hora_inicio']);
        $fin = Carbon::createFromFormat('H:i', $data['hora_fin']);
        $horasNuevas = $inicio->floatDiffInHours($fin);

        if (!$this->validarHorarioLaboral($inicio, $fin)) {
            throw new \DomainException('El permiso debe estar dentro del horario laboral (07:30 a 13:30)');
        }

        $horasDisponiblesReales = $user->horas_disponibles + $permiso->horas_totales;

        if ($horasNuevas > $horasDisponiblesReales) {
            throw new \DomainException('No tenés horas suficientes para modificar este permiso');
        }

        $permiso->update([
            'fecha' => $data['fecha'],
            'hora_inicio' => $data['hora_inicio'],
            'hora_fin' => $data['hora_fin'],
            'horas_totales' => $horasNuevas,
            'motivo' => $data['motivo'],
        ]);

        return $permiso->fresh()->load('usuario', 'estadoRel');
    }

    /**
     * Approves a permission request with transaction safety and pessimistic locking.
     *
     * @throws \InvalidArgumentException if permission is not pending
     * @throws \DomainException if approver is the permission owner or hours are insufficient
     */
    public function aprobar(Permiso $permiso, User $approver): Permiso
    {
        if (!$permiso->esPendiente()) {
            throw new \InvalidArgumentException('El permiso ya fue resuelto');
        }

        if (!$permiso->puedeSerAprobadoPor($approver)) {
            throw new \DomainException('No puedes aprobar tu propio permiso');
        }

        return DB::transaction(function () use ($permiso, $approver) {
            $user = User::lockForUpdate()->find($permiso->user_id);

            if (!$user->tieneHorasSuficientes($permiso->horas_totales)) {
                throw new \DomainException('El empleado ya no tiene horas suficientes');
            }

            $permiso->setEstado(EstadoPermiso::APROBADO);
            $permiso->examinado_por = $approver->id;
            $permiso->examinado_en = now();
            $permiso->save();

            $user->descontarHoras($permiso->horas_totales);

            return $permiso->fresh()->load('usuario', 'examinadoPor', 'estadoRel');
        });
    }

    /**
     * Rejects a permission request with transaction safety.
     *
     * @throws \InvalidArgumentException if permission is not pending
     * @throws \DomainException if rejecter is the permission owner
     */
    public function rechazar(Permiso $permiso, User $rejecter): Permiso
    {
        if (!$permiso->esPendiente()) {
            throw new \InvalidArgumentException('El permiso ya fue resuelto');
        }

        if (!$permiso->puedeSerAprobadoPor($rejecter)) {
            throw new \DomainException('No puedes rechazar tu propio permiso');
        }

        DB::transaction(function () use ($permiso, $rejecter) {
            $permiso->setEstado(EstadoPermiso::RECHAZADO);
            $permiso->examinado_por = $rejecter->id;
            $permiso->examinado_en = now();
            $permiso->save();
        });

        return $permiso->fresh()->load('usuario', 'examinadoPor', 'estadoRel');
    }

    /**
     * Cancels a permission request (employee self-cancellation).
     *
     * @throws \InvalidArgumentException if permission is not pending
     * @throws \DomainException if user is not the permission owner
     */
    public function cancelar(Permiso $permiso, User $owner): Permiso
    {
        if ($permiso->user_id !== $owner->id) {
            throw new \DomainException('Solo puedes cancelar tus propios permisos');
        }

        if (!$permiso->esPendiente()) {
            throw new \InvalidArgumentException('Solo se pueden cancelar permisos pendientes');
        }

        $permiso->setEstado(EstadoPermiso::CANCELADO);
        $permiso->examinado_por = $owner->id;
        $permiso->examinado_en = now();
        $permiso->save();

        return $permiso->fresh()->load('usuario', 'estadoRel');
    }

    /**
     * Validates that times are within working hours (07:30 - 13:30).
     */
    public function validarHorarioLaboral(Carbon $inicio, Carbon $fin): bool
    {
        $inicioLaboral = Carbon::createFromTime(7, 30);
        $finLaboral = Carbon::createFromTime(13, 30);

        return $inicio->greaterThanOrEqualTo($inicioLaboral)
            && $fin->lessThanOrEqualTo($finLaboral);
    }
}

