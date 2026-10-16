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
        Schema::create('progreso_lecciones', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('leccion_id');
            $table->integer('estudiante_id');
            $table->decimal('porcentaje', 5, 2)->default(0);
            $table->boolean('completado')->default(false);
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_completado')->nullable();
            $table->unique(['leccion_id', 'estudiante_id'], 'uk_progreso_leccion_estudiante');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progreso_lecciones');
    }
};
