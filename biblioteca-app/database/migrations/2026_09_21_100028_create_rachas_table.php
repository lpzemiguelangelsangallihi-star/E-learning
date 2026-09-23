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
        Schema::create('rachas', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('estudiante_id');
            $table->integer('racha_actual')->default(0);
            $table->integer('mejor_racha')->default(0);
            $table->date('ultima_fecha')->nullable();
            $table->unique('estudiante_id', 'uk_racha_estudiante');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rachas');
    }
};
