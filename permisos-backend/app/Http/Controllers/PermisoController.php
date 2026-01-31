<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermisoController extends Controller
{
    public function index()
    {
        return Permiso::with('usuario', 'aprobadoPor')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'motivo' => 'required|string',
        ]);

        // Calcular horas totales
        $inicio = Carbon::createFromFormat('H:i', $request->hora_inicio);
        $fin = Carbon::createFromFormat('H:i', $request->hora_fin);

        if (!$this->validarHorarioLaboral($inicio, $fin)) {
            return response()->json([
                'error' => 'El permiso debe estar dentro del horario laboral (07:30 a 13:30)'
            ], 422);
        }

        $horasTotales = $inicio->floatDiffInHours($fin);

        $permiso = Permiso::create([
            'user_id' => Auth::id(),
            'fecha' => $request->fecha,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'horas_totales' => $horasTotales,
            'motivo' => $request->motivo,
            'estado' => Permiso::PENDIENTE,
        ]);

        return response()->json($permiso, 201);
    }


    public function misPermisos()
    {
        return Auth::user()->permisos;
    }

    public function pendientes()
    {
        return Permiso::pendientes()->with('usuario')->get();
    }

    public function aprobar(Permiso $permiso)
    {
        if ($permiso->estado !== Permiso::PENDIENTE) {
            return response()->json(['error' => 'El permiso ya fue resuelto'], 422);
        }

        $permiso->update([
            'estado' => Permiso::APROBADO,
            'aprobado_por' => Auth::id(),
            'aprobado_en' => now(),
        ]);

        return response()->json(['message' => 'Permiso aprobado']);
    }

    public function rechazar(Request $request, Permiso $permiso)
    {
        if ($permiso->estado !== Permiso::PENDIENTE) {
            return response()->json(['error' => 'El permiso ya fue resuelto'], 422);
        }

        $permiso->update([
            'estado' => Permiso::RECHAZADO,
            'aprobado_por' => Auth::id(),
            'aprobado_en' => now(),
        ]);

        return response()->json(['message' => 'Permiso rechazado']);
    }

    public function show(Permiso $permiso)
    {
        // Si es empleado, solo puede ver los propios
        if (Auth::user()->role === 'empleado' && $permiso->user_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return $permiso->load('usuario', 'aprobadoPor');
    }

    public function update(Request $request, Permiso $permiso)
    {
        if ($permiso->user_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        if ($permiso->estado !== Permiso::PENDIENTE) {
            return response()->json(['error' => 'Solo se pueden editar permisos pendientes'], 422);
        }

        $request->validate([
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'motivo' => 'required|string',
        ]);

        $inicio = Carbon::createFromFormat('H:i', $request->hora_inicio);
        $fin = Carbon::createFromFormat('H:i', $request->hora_fin);

        if (!$this->validarHorarioLaboral($inicio, $fin)) {
            return response()->json([
                'error' => 'El permiso debe estar dentro del horario laboral (07:30 a 13:30)'
            ], 422);
        }

        $horasTotales = $fin->floatDiffInHours($inicio);

        $permiso->update([
            'fecha' => $request->fecha,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'horas_totales' => $horasTotales,
            'motivo' => $request->motivo,
        ]);

        return response()->json([
            'message' => 'Permiso actualizado correctamente',
            'permiso' => $permiso
        ]);
    }

    public function destroy(Permiso $permiso)
    {
        if ($permiso->user_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        if ($permiso->estado !== Permiso::PENDIENTE) {
            return response()->json(['error' => 'No se puede eliminar un permiso ya resuelto'], 422);
        }

        $permiso->delete();

        return response()->json(['message' => 'Permiso eliminado correctamente']);
    }
    private function validarHorarioLaboral(Carbon $inicio, Carbon $fin)
    {
        $inicioLaboral = Carbon::createFromTime(7, 30);
        $finLaboral    = Carbon::createFromTime(13, 30);

        return $inicio->greaterThanOrEqualTo($inicioLaboral)
            && $fin->lessThanOrEqualTo($finLaboral);
    }
}
