<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iop_transiciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seguimiento_id')
                ->constrained('iop_seguimientos')
                ->restrictOnDelete();

            $table->unsignedInteger('ciclo');

            $table->string('estado_anterior', 2);
            $table->string('estado_siguiente', 2);

            $table->decimal('calificacion_anterior', 5, 2);
            $table->decimal('calificacion_nueva', 5, 2);

            // Permite distinguir pruebas de evaluaciones reales.
            $table->string('origen', 20)
                ->default('simulacion');

            // Identificador del evento de evaluación,
            // cuando integremos el módulo de Miguel.
            $table->string('referencia_externa', 100)
                ->nullable()
                ->unique();

            $table->timestamps();

            // Evita registrar el mismo ciclo dos veces.
            $table->unique(
                ['seguimiento_id', 'ciclo'],
                'iop_transicion_seguimiento_ciclo_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iop_transiciones');
    }
};
