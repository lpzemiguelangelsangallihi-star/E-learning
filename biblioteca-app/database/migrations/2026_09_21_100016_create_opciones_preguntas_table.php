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
        Schema::create('opciones_preguntas', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('pregunta_id');
            $table->text('opcion');
            $table->boolean('es_correcta')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opciones_preguntas');
    }
};
