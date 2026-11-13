<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('iop_asignaciones_refuerzo', function (Blueprint $table) {
            $table->text('respuesta_estudiante')->nullable();

            $table->timestamp('entregado_en')->nullable();

            $table->text('retroalimentacion')->nullable();

            $table->foreignId('revisado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('revisado_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('iop_asignaciones_refuerzo', function (Blueprint $table) {
            $table->dropForeign(['revisado_por']);

            $table->dropColumn([
                'respuesta_estudiante',
                'entregado_en',
                'retroalimentacion',
                'revisado_por',
                'revisado_en',
            ]);
        });
    }
};
