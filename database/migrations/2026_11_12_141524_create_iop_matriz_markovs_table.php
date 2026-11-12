<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iop_matrices_markov', function (Blueprint $table) {
            $table->id();

            // lecciones.id utiliza INT con signo.
            // NULL permitirá matrices globales en el futuro.
            $table->integer('leccion_id')->nullable();

            $table->foreign('leccion_id')
                ->references('id')
                ->on('lecciones')
                ->restrictOnDelete();

            $table->string('origen', 20);

            $table->unsignedInteger('version')->default(1);

            $table->json('conteos');

            // Puede contener filas null cuando faltan datos.
            $table->json('probabilidades');

            $table->json('totales');

            $table->boolean('completa')->default(false);

            $table->unsignedInteger('minimo_observaciones')
                ->default(1);

            $table->timestamp('fecha_calculo');

            $table->timestamps();

            $table->index([
                'leccion_id',
                'origen',
                'version',
            ], 'iop_markov_alcance_version_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iop_matrices_markov');
    }
};
