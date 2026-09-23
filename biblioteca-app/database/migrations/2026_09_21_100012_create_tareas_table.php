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
        Schema::create('tareas', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('leccion_id');
            $table->integer('profesor_id');
            $table->string('titulo', 200);
            $table->text('instrucciones')->nullable();
            $table->dateTime('fecha_limite')->nullable();
            $table->decimal('puntaje_maximo', 5, 2)->default(100);
            $table->string('archivo', 500)->nullable();
            $table->enum('estado', ['borrador', 'publicada', 'cerrada'])->default('borrador');
            $table->timestamp('creado_en')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
