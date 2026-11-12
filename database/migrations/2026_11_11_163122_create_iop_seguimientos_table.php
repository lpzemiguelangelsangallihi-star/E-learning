<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iop_seguimientos', function (Blueprint $table) {
            $table->id();

            // users.id es BIGINT UNSIGNED.
            $table->foreignId('estudiante_id')
                ->constrained('users')
                ->restrictOnDelete();

            // lecciones.id es INT con signo.
            $table->integer('leccion_id');

            $table->foreign('leccion_id')
                ->references('id')
                ->on('lecciones')
                ->restrictOnDelete();

            // Recomendación que inició el seguimiento.
            $table->foreignId('recomendacion_inicial_id')
                ->constrained('iop_recomendaciones')
                ->restrictOnDelete();

            $table->decimal('calificacion_inicial', 5, 2);

            $table->string('estado_inicial', 2);
            $table->string('estado_actual', 2);

            // Vector de probabilidades inicial.
            $table->json('vector_inicial');

            $table->unsignedInteger('ciclo_actual')
                ->default(0);

            $table->decimal('ultima_calificacion', 5, 2);

            $table->boolean('activo')->default(true);

            // Se utilizará cuando definamos S4.
            $table->timestamp('consolidado_en')->nullable();

            $table->timestamps();

            // Un seguimiento por estudiante y lección
            // durante esta primera versión del módulo.
            $table->unique(
                ['estudiante_id', 'leccion_id'],
                'iop_seguimiento_estudiante_leccion_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iop_seguimientos');
    }
};
