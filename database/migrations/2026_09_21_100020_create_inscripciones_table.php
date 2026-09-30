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
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('curso_id');
            $table->integer('estudiante_id');
            $table->timestamp('fecha_inscripcion')->useCurrent();
            $table->enum('estado', ['activo', 'finalizado', 'cancelado'])->default('activo');
            $table->unique(['curso_id', 'estudiante_id'], 'uk_inscripcion_curso_estudiante');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};
