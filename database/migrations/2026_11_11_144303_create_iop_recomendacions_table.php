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
    Schema::create('iop_recomendaciones', function (Blueprint $table) {
        $table->id();

        // users.id = BIGINT UNSIGNED
        $table->foreignId('estudiante_id')
            ->constrained('users')
            ->restrictOnDelete();

        // lecciones.id = INT con signo
        $table->integer('leccion_id');

        $table->foreign('leccion_id')
            ->references('id')
            ->on('lecciones')
            ->restrictOnDelete();

        // iop_modelos_decision.id = BIGINT UNSIGNED
        $table->foreignId('modelo_decision_id')
            ->constrained('iop_modelos_decision')
            ->restrictOnDelete();

        $table->decimal('calificacion_inicial', 5, 2);

        $table->string('alternativa_recomendada', 30);

        $table->decimal('valor_maximin', 10, 2);

        $table->json('minimos');

        $table->json('matriz_utilizada');

        $table->timestamps();

        $table->index(['estudiante_id', 'leccion_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iop_recomendaciones');
    }
};
