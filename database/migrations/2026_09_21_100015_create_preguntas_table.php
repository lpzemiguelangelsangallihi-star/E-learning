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
        Schema::create('preguntas', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('examen_id');
            $table->text('pregunta');
            $table->enum('tipo', ['opcion_multiple', 'verdadero_falso', 'respuesta_corta', 'varias_respuestas']);
            $table->decimal('puntaje', 5, 2)->default(1);
            $table->integer('orden')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preguntas');
    }
};
