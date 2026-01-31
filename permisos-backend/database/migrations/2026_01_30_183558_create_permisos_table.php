<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();

            // Relación con el empleado que solicita el permiso
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Fecha y horario del permiso
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');

            // Cantidad de horas calculadas automáticamente
            $table->decimal('horas_totales', 4, 2);

            // Motivo del permiso
            $table->text('motivo');

            // Estado del permiso
            $table->string('estado')->default('pendiente'); // pendiente | aprobado | rechazado

            // Auditoría de aprobación
            $table->foreignId('aprobado_por')
                ->nullable()
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->timestamp('aprobado_en')->nullable();

            $table->timestamps();

            // Índices útiles
            $table->index(['user_id', 'fecha']);
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permisos');
    }
};
