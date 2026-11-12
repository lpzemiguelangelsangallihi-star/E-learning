<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('iop_seguimientos', function (Blueprint $table) {
            // intentos_examen.id es INT con signo.
            $table->integer('intento_diagnostico_id')
                ->nullable();

            $table->foreign(
                'intento_diagnostico_id',
                'iop_seg_diag_intento_fk'
            )
                ->references('id')
                ->on('intentos_examen')
                ->restrictOnDelete();

            $table->unique(
                'intento_diagnostico_id',
                'iop_seg_diag_intento_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('iop_seguimientos', function (Blueprint $table) {
            $table->dropUnique('iop_seg_diag_intento_unique');
            $table->dropForeign('iop_seg_diag_intento_fk');
            $table->dropColumn('intento_diagnostico_id');
        });
    }
};
