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
        Schema::create('usuario_experiencia', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('estudiante_id');
            $table->integer('experiencia_total')->default(0);
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->unique('estudiante_id', 'uk_experiencia_estudiante');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_experiencia');
    }
};
