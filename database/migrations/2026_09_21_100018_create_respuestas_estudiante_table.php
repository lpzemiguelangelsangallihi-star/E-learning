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
        Schema::create('respuestas_estudiante', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('intento_id');
            $table->integer('pregunta_id');
            $table->integer('opcion_id')->nullable();
            $table->text('respuesta_texto')->nullable();
            $table->boolean('es_correcta')->nullable();
            $table->decimal('puntaje_obtenido', 5, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respuestas_estudiante');
    }
};
