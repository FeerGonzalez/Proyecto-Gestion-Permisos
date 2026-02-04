<?php

namespace Tests\Unit\Services;

use App\Models\EstadoPermiso;
use App\Models\Permiso;
use App\Models\User;
use App\Services\PermisoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermisoServiceTest extends TestCase
{
    use RefreshDatabase;

    private PermisoService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\EstadosPermisoSeeder::class);

        $this->service = app(PermisoService::class);
    }

    public function test_crear_permiso_valida_horario_laboral()
    {
        $usuario = User::factory()->create([
            'horas_disponibles' => 10
        ]);

        $this->expectException(\DomainException::class);

        $this->service->crearPermiso($usuario, [
            'fecha' => now()->toDateString(),
            'hora_inicio' => '06:00',
            'hora_fin' => '08:00',
            'motivo' => 'Fuera de horario'
        ]);
    }

    public function test_crear_permiso_valida_saldo_de_horas()
    {
        $usuario = User::factory()->create([
            'horas_disponibles' => 1
        ]);

        $this->expectException(\DomainException::class);

        $this->service->crearPermiso($usuario, [
            'fecha' => now()->toDateString(),
            'hora_inicio' => '08:00',
            'hora_fin' => '12:00',
            'motivo' => 'Sin horas suficientes'
        ]);
    }

    public function test_aprobar_permiso_descuenta_horas_con_bloqueo_pesimista()
    {

        $empleado = User::factory()->create([
            'horas_disponibles' => 8
        ]);

        $supervisor = User::factory()->create([
            'role' => 'supervisor'
        ]);

        $permiso = Permiso::factory()->create([
            'user_id' => $empleado->id,
            'estado_id' => EstadoPermiso::pendiente()->id,
            'horas_totales' => 3
        ]);

        $permisoAprobado = $this->service->aprobar($permiso, $supervisor);

        $this->assertEquals(
            EstadoPermiso::APROBADO,
            $permisoAprobado->estadoRel->nombre
        );

        $this->assertEquals(
            5,
            $empleado->fresh()->horas_disponibles
        );
    }

    public function test_cancelar_permiso_requiere_ser_el_duenio()
    {
        $duenio = User::factory()->create();
        $otroUsuario = User::factory()->create();

        $permiso = Permiso::factory()->create([
            'user_id' => $duenio->id,
            'estado_id' => EstadoPermiso::pendiente()->id
        ]);

        $this->expectException(\DomainException::class);

        $this->service->cancelar($permiso, $otroUsuario);
    }
}
