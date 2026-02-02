<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EstadoPermiso;

class PermisoController extends Controller
{
    public function index()
    {
        return Permiso::with('usuario', 'examinadoPor')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function store(Request $request){
        $data = $this->validarYCalcularDatos($request);

        if (!$data['user']->tieneHorasSuficientes($data['horas_totales'])) {
            return response()->json([
                'error' => 'No tenés horas suficientes para solicitar este permiso',
                'horas_disponibles' => $data['user']->horas_disponibles,
                'horas_solicitadas' => $data['horas_totales'],
            ], 422);
        }

        $permiso = Permiso::create([
            'user_id' => $data['user']->id,
            'fecha' => $request->fecha,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'horas_totales' => $data['horas_totales'],
            'motivo' => $request->motivo,
            'estado' => 'pendiente',
        ]);

        return response()->json($permiso, 201);
    }


    public function misPermisos()
    {
        return Auth::user()->permisos;
    }

    public function pendientes()
    {
        return Permiso::with('usuario')
            ->where('estado', 'pendiente')
            ->get();
    }

    public function aprobar(Permiso $permiso)
    {
        if (!$permiso->esPendiente()) {
            return response()->json(['error' => 'El permiso ya fue resuelto'], 422);
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

        return response()->json([
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

        return response()->json(['message' => 'Permiso rechazado']);
    }

    public function show(Permiso $permiso)
    {
        // Si es empleado, solo puede ver los propios
        if (Auth::user()->role === 'empleado' && $permiso->user_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return $permiso->load('usuario', 'examinadoPor');
    }

    public function update(Request $request, Permiso $permiso)
    {
        if ($permiso->user_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        if ($permiso->estado !== 'pendiente') {
            return response()->json([
                'error' => 'Solo se pueden editar permisos pendientes'
            ], 422);
        }

        $data = $this->validarYCalcularDatos($request);

        $horasDisponiblesReales =
            $data['user']->horas_disponibles + $permiso->horas_totales;

        if ($data['horas_totales'] > $horasDisponiblesReales) {
            return response()->json([
                'error' => 'No tenés horas suficientes para modificar este permiso',
                'horas_disponibles' => $data['user']->horas_disponibles,
                'horas_originales' => $permiso->horas_totales,
                'horas_nuevas' => $data['horas_totales'],
            ], 422);
        }

        $permiso->update([
            'fecha' => $request->fecha,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'horas_totales' => $data['horas_totales'],
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

        if ($permiso->estado !== 'pendiente') {
            return response()->json([
                'error' => 'No se puede eliminar un permiso ya resuelto'
            ], 422);
        }

        $permiso->delete();

        return response()->json([
            'message' => 'Permiso eliminado correctamente'
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

        return response()->json([
            'message' => 'Permiso cancelado correctamente',
            'permiso' => $permiso
        ]);
    }

    private function validarHorarioLaboral(Carbon $inicio, Carbon $fin)
    {
        $inicioLaboral = Carbon::createFromTime(7, 30);
        $finLaboral    = Carbon::createFromTime(13, 30);

        return $inicio->greaterThanOrEqualTo($inicioLaboral)
            && $fin->lessThanOrEqualTo($finLaboral);
    }

    private function validarYCalcularDatos(Request $request): array
    {
        $request->validate([
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'motivo' => 'required|string',
        ]);

        $inicio = Carbon::createFromFormat('H:i', $request->hora_inicio);
        $fin = Carbon::createFromFormat('H:i', $request->hora_fin);

        if (!$this->validarHorarioLaboral($inicio, $fin)) {
            abort(
                response()->json([
                    'error' => 'El permiso debe estar dentro del horario laboral (07:30 a 13:30)'
                ], 422)
            );
        }

        return [
            'inicio' => $inicio,
            'fin' => $fin,
            'horas_totales' => $inicio->floatDiffInHours($fin),
            'user' => Auth::user(),
        ];
    }
}
