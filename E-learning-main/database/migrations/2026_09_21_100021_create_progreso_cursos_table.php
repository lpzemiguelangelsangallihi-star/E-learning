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
        Schema::create('progreso_cursos', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('curso_id');
            $table->integer('estudiante_id');
            $table->decimal('porcentaje', 5, 2)->default(0);
            $table->integer('lecciones_completadas')->default(0);
            $table->enum('estado', ['no_iniciado', 'en_progreso', 'completado'])->default('no_iniciado');
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_finalizacion')->nullable();
            $table->unique(['curso_id', 'estudiante_id'], 'uk_progreso_curso_estudiante');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progreso_cursos');
    }
};
