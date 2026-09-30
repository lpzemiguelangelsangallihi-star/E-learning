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
        Schema::create('cursos', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('categoria_id')->nullable();
            $table->integer('materia_id')->nullable();
            $table->integer('profesor_id');
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->text('objetivos')->nullable();
            $table->string('imagen', 255)->nullable();
            $table->enum('nivel', ['basico', 'intermedio', 'avanzado'])->default('basico');
            $table->integer('duracion')->default(0);
            $table->enum('tipo', ['gratuito', 'pago'])->default('gratuito');
            $table->decimal('precio', 10, 2)->default(0);
            $table->enum('estado', ['borrador', 'publicado', 'oculto'])->default('borrador');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
