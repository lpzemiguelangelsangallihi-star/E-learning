<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tipo de evaluación para IOP:
 *  - ninguno:     no participa en IOP (valor de los exámenes existentes).
 *  - diagnostico: primera evaluación; genera la recomendación e inicia el seguimiento.
 *  - refuerzo:    evaluación posterior a un ciclo de refuerzo; registra una transición.
 *  - ordinaria:   evaluación normal del curso; no afecta a IOP.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('examenes', 'tipo_iop')) {
            Schema::table('examenes', function (Blueprint $table) {
                $table->string('tipo_iop', 20)->default('ninguno');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('examenes', 'tipo_iop')) {
            Schema::table('examenes', function (Blueprint $table) {
                $table->dropColumn('tipo_iop');
            });
        }
    }
};
