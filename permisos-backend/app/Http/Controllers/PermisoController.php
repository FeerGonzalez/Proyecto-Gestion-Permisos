<?php

namespace App\Http\Controllers;

use App\Http\Requests\Permisos\StorePermisoRequest;
use App\Http\Requests\Permisos\UpdatePermisoRequest;
use App\Http\Resources\Dto\PermisoResource;
use App\Models\EstadoPermiso;
use App\Models\Permiso;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermisoController extends Controller
{
    public function index()
    {
        $permisos = Permiso::with('usuario', 'examinadoPor', 'estadoRel')
            ->orderBy('created_at', 'desc')
            ->get();

        return PermisoResource::collection($permisos);
    }

    public function store(StorePermisoRequest  $request){
        $inicio = $request->getInicio();
        $fin = $request->getFin();

        if (!$this->validarHorarioLaboral($inicio, $fin)) {
            return response()->json([
                'error' => 'El permiso debe estar dentro del horario laboral (07:30 a 13:30)'
            ], 422);
        }

        $user = $request->user();
        $horas = $request->horasTotales();

        if (!$user->tieneHorasSuficientes($horas)) {
            return response()->json([
                'error' => 'No tenés horas suficientes para solicitar este permiso',
                'horas_disponibles' => $user->horas_disponibles,
                'horas_solicitadas' => $horas,
            ], 422);
        }

        $permiso = Permiso::create([
            'user_id' => $user->id,
            'fecha' => $request->fecha,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'horas_totales' => $horas,
            'motivo' => $request->motivo,
            'estado_id' => EstadoPermiso::pendiente()->id,
        ]);


        return (new PermisoResource($permiso->load('usuario')))
            ->response()
            ->setStatusCode(201);
    }


    public function misPermisos()
    {
        $permisos = Auth::user()
            ->permisos()
            ->with('estadoRel')
            ->orderByDesc('created_at')
            ->get();

        return PermisoResource::collection($permisos);
    }

    public function pendientes()
    {
        $permisos = Permiso::with('usuario', 'estadoRel')
            ->whereHas('estadoRel', function ($q) {
                $q->where('nombre', EstadoPermiso::PENDIENTE);
            })
            ->get();

        return PermisoResource::collection($permisos);
    }

    public function aprobar(Permiso $permiso)
    {
        if (!$permiso->esPendiente()) {
            return response()->json(['error' => 'El permiso ya fue resuelto'], 422);
        }

        if (! $permiso->puedeSerAprobadoPor(Auth::user())) {
            return response()->json([
                'error' => 'No puedes aprobar tu propio permiso'
            ], 422);
        }

        $user = $permiso->usuario;

        if (!$user->tieneHorasSuficientes($permiso->horas_totales)) {
            return response()->json(['error' => 'El empleado ya no tiene horas suficientes'], 422);
        }

        $permiso->setEstado(EstadoPermiso::APROBADO);
        $permiso->examinado_por = Auth::id();
        $permiso->examinado_en = now();
        $permiso->save();

        $user->descontarHoras($permiso->horas_totales);

        return (new PermisoResource(
            $permiso->load('usuario', 'examinadoPor')
        ))->additional([
            'message' => 'Permiso aprobado correctamente',
            'horas_restantes' => $user->horas_disponibles,
        ]);
    }

    public function rechazar(Request $request, Permiso $permiso)
    {
        if (!$permiso->esPendiente()) {
            return response()->json(['error' => 'El permiso ya fue resuelto'], 422);
        }

        $permiso->setEstado(EstadoPermiso::RECHAZADO);
        $permiso->examinado_por = Auth::id();
        $permiso->examinado_en = now();
        $permiso->save();

        return (new PermisoResource(
            $permiso->load('usuario', 'examinadoPor')
        ))->additional([
            'message' => 'Permiso rechazado correctamente'
        ]);
    }

    public function show(Permiso $permiso)
    {
        // Si es empleado, solo puede ver los propios
        if (Auth::user()->role === 'empleado' && $permiso->user_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return new PermisoResource(
            $permiso->load('usuario', 'examinadoPor')
        );
    }

    public function update(UpdatePermisoRequest  $request, Permiso $permiso)
    {
        $inicio = $request->getInicio();
        $fin = $request->getFin();

        if (!$this->validarHorarioLaboral($inicio, $fin)) {
            return response()->json([
                'error' => 'El permiso debe estar dentro del horario laboral (07:30 a 13:30)'
            ], 422);
        }

        $user = $request->user();
        $horasNuevas = $request->horasTotales();

        $horasDisponiblesReales =
            $user->horas_disponibles + $permiso->horas_totales;

        if ($horasNuevas > $horasDisponiblesReales) {
            return response()->json([
                'error' => 'No tenés horas suficientes para modificar este permiso',
            ], 422);
        }

        $permiso->update([
            'fecha' => $request->fecha,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'horas_totales' => $horasNuevas,
            'motivo' => $request->motivo,
        ]);

        return (new PermisoResource(
            $permiso->fresh()->load('usuario')
        ))->additional([
            'message' => 'Permiso actualizado correctamente'
        ]);
    }

    public function cancelar(Permiso $permiso)
    {
        // Solo el dueño del permiso puede cancelarlo
        if ($permiso->user_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        if (!$permiso->esPendiente()) {
            return response()->json(['error' => 'Solo se pueden cancelar permisos pendientes'], 422);
        }

        $permiso->setEstado(EstadoPermiso::CANCELADO);
        $permiso->examinado_por = Auth::id(); // el propio empleado
        $permiso->examinado_en = now();
        $permiso->save();

        return (new PermisoResource(
            $permiso->load('usuario')
        ))->additional([
            'message' => 'Permiso cancelado correctamente'
        ]);
    }

    private function validarHorarioLaboral(Carbon $inicio, Carbon $fin)
    {
        $inicioLaboral = Carbon::createFromTime(7, 30);
        $finLaboral    = Carbon::createFromTime(13, 30);

        return $inicio->greaterThanOrEqualTo($inicioLaboral)
            && $fin->lessThanOrEqualTo($finLaboral);
    }
}
