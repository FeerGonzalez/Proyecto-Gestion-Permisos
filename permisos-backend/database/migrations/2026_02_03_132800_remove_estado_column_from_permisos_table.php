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

            if (Schema::hasColumn('permisos', 'estado')) {
                $table->dropIndex(['estado']);
            }

            $table->dropColumn('estado');

            $table->foreignId('estado_id')
                ->nullable(false)
                ->change();

            $table->index('estado_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permisos', function (Blueprint $table) {

            $table->string('estado')->default('pendiente');

            $table->dropIndex(['estado_id']);

            $table->foreignId('estado_id')
                ->nullable()
                ->change();
        });
    }
};
