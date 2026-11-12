<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Refuerza las tablas académicas existentes (no crea tablas nuevas):
 *  - Evita dos intentos con el mismo número para el mismo estudiante.
 *  - Índice para consultar rápido las respuestas de un intento.
 *  - Permite al profesor decidir si el estudiante ve las respuestas correctas.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('intentos_examen', 'uk_intento_examen_estudiante_numero', 'unique')) {
            Schema::table('intentos_examen', function (Blueprint $table) {
                $table->unique(
                    ['examen_id', 'estudiante_id', 'numero_intento'],
                    'uk_intento_examen_estudiante_numero'
                );
            });
        }

        if (! Schema::hasIndex('respuestas_estudiante', 'idx_respuesta_intento_pregunta')) {
            Schema::table('respuestas_estudiante', function (Blueprint $table) {
                $table->index(['intento_id', 'pregunta_id'], 'idx_respuesta_intento_pregunta');
            });
        }

        if (! Schema::hasColumn('examenes', 'mostrar_correctas')) {
            Schema::table('examenes', function (Blueprint $table) {
                $table->boolean('mostrar_correctas')->default(true);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('examenes', 'mostrar_correctas')) {
            Schema::table('examenes', function (Blueprint $table) {
                $table->dropColumn('mostrar_correctas');
            });
        }

        if (Schema::hasIndex('respuestas_estudiante', 'idx_respuesta_intento_pregunta')) {
            Schema::table('respuestas_estudiante', function (Blueprint $table) {
                $table->dropIndex('idx_respuesta_intento_pregunta');
            });
        }

        if (Schema::hasIndex('intentos_examen', 'uk_intento_examen_estudiante_numero', 'unique')) {
            Schema::table('intentos_examen', function (Blueprint $table) {
                $table->dropUnique('uk_intento_examen_estudiante_numero');
            });
        }
    }
};
