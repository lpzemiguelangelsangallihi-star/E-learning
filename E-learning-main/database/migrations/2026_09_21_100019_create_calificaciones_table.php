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
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('estudiante_id');
            $table->integer('profesor_id');
            $table->integer('curso_id');
            $table->integer('tarea_id')->nullable();
            $table->integer('examen_id')->nullable();
            $table->enum('tipo', ['tarea', 'examen', 'actividad']);
            $table->decimal('calificacion', 5, 2);
            $table->text('retroalimentacion')->nullable();
            $table->dateTime('fecha')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};
