<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iop_ciclos_refuerzo', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seguimiento_id')
                ->constrained('iop_seguimientos')
                ->restrictOnDelete();

            // Ciclo 0 = diagnóstico inicial.
            $table->unsignedInteger('ciclo');

            $table->foreignId('recomendacion_id')
                ->constrained('iop_recomendaciones')
                ->restrictOnDelete();

            // Transición que originó el siguiente refuerzo.
            // Es NULL solamente para el ciclo inicial.
            $table->foreignId('transicion_origen_id')
                ->nullable()
                ->constrained('iop_transiciones')
                ->restrictOnDelete();

            $table->timestamps();

            $table->unique(
                ['seguimiento_id', 'ciclo'],
                'iop_ciclos_seguimiento_ciclo_unique'
            );

            $table->unique(
                'recomendacion_id',
                'iop_ciclos_recomendacion_unique'
            );

            $table->unique(
                'transicion_origen_id',
                'iop_ciclos_transicion_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iop_ciclos_refuerzo');
    }
};
