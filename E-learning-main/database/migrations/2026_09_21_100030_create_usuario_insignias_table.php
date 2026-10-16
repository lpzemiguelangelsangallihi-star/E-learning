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
        Schema::create('usuario_insignias', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('estudiante_id');
            $table->integer('insignia_id');
            $table->dateTime('fecha_obtencion')->useCurrent();
            $table->unique(['estudiante_id', 'insignia_id'], 'uk_usuario_insignia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_insignias');
    }
};
