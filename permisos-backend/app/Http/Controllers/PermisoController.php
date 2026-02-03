<?php

namespace App\Http\Controllers;

use App\Http\Requests\Permisos\StorePermisoRequest;
use App\Http\Requests\Permisos\UpdatePermisoRequest;
use App\Http\Resources\Dto\PermisoResource;
use App\Models\EstadoPermiso;
use App\Models\Permiso;
use App\Services\PermisoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermisoController extends Controller
{
    private PermisoService $permisoService;

    public function index()
    {
        $permisos = Permiso::with('usuario', 'examinadoPor', 'estadoRel')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return PermisoResource::collection($permisos);
    }

    public function store(StorePermisoRequest  $request){
        $inicio = $request->getInicio();
        $fin = $request->getFin();

        if (!$this->permisoService->validarHorarioLaboral($inicio, $fin)) {
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
            ->paginate(15);

        return PermisoResource::collection($permisos);
    }

    public function pendientes()
    {
        $permisos = Permiso::with('usuario', 'estadoRel')
            ->pendientes()
            ->orderByDesc('created_at')
            ->paginate(15);

        return PermisoResource::collection($permisos);
    }

    public function aprobar(Permiso $permiso)
    {
        try {
            $permiso = $this->permisoService->aprobar($permiso, Auth::user());

            return (new PermisoResource($permiso))->additional([
                'message' => 'Permiso aprobado correctamente',
                'horas_restantes' => $permiso->usuario->horas_disponibles,
            ]);

        } catch (\DomainException | \InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }


    public function rechazar(Request $request, Permiso $permiso)
    {
        try {
            $permiso = $this->permisoService->rechazar($permiso, Auth::user());

            return (new PermisoResource($permiso))->additional([
                'message' => 'Permiso rechazado correctamente'
            ]);

        } catch (\DomainException | \InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
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

        if (!$this->permisoService->validarHorarioLaboral($inicio, $fin)) {
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

    public function gestionadosPorMi()
    {
        $permisos = Permiso::with('usuario', 'estadoRel')
            ->examinadoPorUsuario(Auth::id())
            ->gestionados()
            ->orderByDesc('examinado_en')
            ->paginate(15);

        return PermisoResource::collection($permisos);
    }

    public function cancelar(Permiso $permiso)
    {
        try {
            $permiso = $this->permisoService->cancelar($permiso, Auth::user());

            return (new PermisoResource($permiso))->additional([
                'message' => 'Permiso cancelado correctamente'
            ]);

        } catch (\DomainException | \InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
