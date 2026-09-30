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
        Schema::create('examenes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('leccion_id');
            $table->integer('profesor_id');
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->integer('tiempo_limite')->nullable();
            $table->integer('intentos_permitidos')->default(1);
            $table->decimal('puntaje_maximo', 5, 2)->default(100);
            $table->enum('estado', ['borrador', 'publicado', 'cerrado'])->default('borrador');
            $table->timestamp('creado_en')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examenes');
    }
};
