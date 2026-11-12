<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iop_asignaciones_refuerzo', function (Blueprint $table) {
            $table->id();

            // Cada asignación nace de una recomendación Maximin.
            $table->foreignId('recomendacion_id')
                ->constrained('iop_recomendaciones')
                ->restrictOnDelete();

            // El ejercicio corresponde al catálogo IOP.
            $table->foreignId('ejercicio_id')
                ->constrained('iop_ejercicios_refuerzo')
                ->restrictOnDelete();

            $table->string('estado', 20)->default('asignado');

            $table->timestamp('asignado_en');

            // Se usará cuando se implemente la verificación
            // académica del refuerzo.
            $table->timestamp('completado_en')->nullable();

            $table->timestamps();

            // Una asignación por recomendación en esta versión.
            $table->unique(
                'recomendacion_id',
                'iop_asignacion_recomendacion_unique'
            );

            $table->index(
                ['ejercicio_id', 'estado'],
                'iop_asignacion_ejercicio_estado_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iop_asignaciones_refuerzo');
    }
};
