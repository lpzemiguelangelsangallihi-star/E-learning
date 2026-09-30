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
        Schema::create('entregas', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('tarea_id');
            $table->integer('estudiante_id');
            $table->string('archivo', 500)->nullable();
            $table->text('respuesta')->nullable();
            $table->dateTime('fecha_entrega')->useCurrent();
            $table->enum('estado', ['entregada', 'calificada', 'tarde'])->default('entregada');
            $table->decimal('calificacion', 5, 2)->nullable();
            $table->text('retroalimentacion')->nullable();
            $table->unique(['tarea_id', 'estudiante_id'], 'uk_entrega_tarea_estudiante');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entregas');
    }
};
