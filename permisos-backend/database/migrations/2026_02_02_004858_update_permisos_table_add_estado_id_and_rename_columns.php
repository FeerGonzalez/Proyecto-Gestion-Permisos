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
        Schema::table('permisos', function (Blueprint $table) {

            // Nuevo FK al estado
            $table->foreignId('estado_id')
                ->nullable()
                ->after('estado')
                ->constrained('estados_permiso');

            // Auditoría (nombres más claros)
            $table->renameColumn('aprobado_por', 'examinado_por');
            $table->renameColumn('aprobado_en', 'examinado_en');
        });

        // Migrar estado string → estado_id
        DB::statement("
            UPDATE permisos
            SET estado_id = (
                SELECT id
                FROM estados_permiso
                WHERE estados_permiso.nombre = permisos.estado
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
