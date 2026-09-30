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
        Schema::create('intentos_examen', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('examen_id');
            $table->integer('estudiante_id');
            $table->integer('numero_intento')->default(1);
            $table->dateTime('fecha_inicio')->useCurrent();
            $table->dateTime('fecha_fin')->nullable();
            $table->decimal('calificacion', 5, 2)->nullable();
            $table->enum('estado', ['en_proceso', 'finalizado'])->default('en_proceso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intentos_examen');
    }
};
