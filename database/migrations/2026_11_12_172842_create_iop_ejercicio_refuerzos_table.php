<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iop_ejercicios_refuerzo', function (Blueprint $table) {
            $table->id();

            // lecciones.id es INT con signo.
            $table->integer('leccion_id');

            $table->foreign('leccion_id')
                ->references('id')
                ->on('lecciones')
                ->restrictOnDelete();

            $table->string('titulo', 200);

            $table->text('descripcion');

            // Categoría que utiliza Maximin.
            $table->string('tipo', 20);

            $table->text('instrucciones');

            $table->string('url_recurso', 2048)->nullable();

            $table->unsignedInteger('orden')->default(1);

            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->index([
                'leccion_id',
                'tipo',
                'activo',
            ], 'iop_ejercicios_busqueda_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iop_ejercicios_refuerzo');
    }
};
