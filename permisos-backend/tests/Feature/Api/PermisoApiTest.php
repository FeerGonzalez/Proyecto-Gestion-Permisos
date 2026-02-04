<?php

namespace Tests\Feature\Api;

use App\Models\EstadoPermiso;
use App\Models\Permiso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PermisoApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\EstadosPermisoSeeder::class);
    }

    public function test_empleado_puede_crear_un_permiso()
    {
        $usuario = User::factory()->create([
            'role' => 'empleado',
            'horas_disponibles' => 8
        ]);

        Sanctum::actingAs($usuario, [], 'sanctum');

        $response = $this->postJson('/api/permisos', [
            'fecha' => now()->toDateString(),
            'hora_inicio' => '08:00',
            'hora_fin' => '10:00',
            'motivo' => 'Trámite'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data']);

        $this->assertDatabaseHas('permisos', [
            'user_id' => $usuario->id
        ]);
    }

    public function test_supervisor_puede_aprobar_un_permiso()
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
            'horas_totales' => 2
        ]);

        Sanctum::actingAs($supervisor, [], 'sanctum');

        $response = $this->postJson("/api/permisos/{$permiso->id}/aprobar");

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Permiso aprobado correctamente'
            ]);
    }

    public function test_empleado_no_puede_aprobar_permisos()
    {
        $empleado = User::factory()->create([
            'role' => 'empleado'
        ]);

        $permiso = Permiso::factory()->create([
            'user_id' => $empleado->id,
            'estado_id' => EstadoPermiso::pendiente()->id
        ]);

        Sanctum::actingAs($empleado, [], 'sanctum');

        $response = $this->postJson("/api/permisos/{$permiso->id}/aprobar");

        $response->assertStatus(403);
    }
}
