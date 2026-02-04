<?php

namespace App\Http\Controllers;

use App\Http\Requests\Permisos\StorePermisoRequest;
use App\Http\Requests\Permisos\UpdatePermisoRequest;
use App\Http\Resources\Dto\PermisoResource;
use App\Models\Permiso;
use App\Services\PermisoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermisoController extends Controller
{
    public function __construct(private PermisoService $permisoService)
    {
    }

    public function index()
    {
        $permisos = Permiso::with('usuario', 'examinadoPor', 'estadoRel')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return PermisoResource::collection($permisos);
    }

    public function store(StorePermisoRequest $request)
    {
        try {
            $permiso = $this->permisoService->crearPermiso(
                $request->user(),
                $request->validated()
            );

            return (new PermisoResource($permiso))
                ->response()
                ->setStatusCode(201);

        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
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

    public function update(UpdatePermisoRequest $request, Permiso $permiso)
    {
        try {
            $permiso = $this->permisoService->actualizarPermiso(
                $permiso,
                $request->user(),
                $request->validated()
            );

            return (new PermisoResource($permiso))->additional([
                'message' => 'Permiso actualizado correctamente'
            ]);

        } catch (\DomainException | \InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
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
