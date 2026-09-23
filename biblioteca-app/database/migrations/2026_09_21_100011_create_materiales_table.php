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
        Schema::create('materiales', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('leccion_id');
            $table->string('titulo', 200);
            $table->enum('tipo', ['pdf', 'imagen', 'presentacion', 'documento', 'enlace', 'otro']);
            $table->string('archivo', 500)->nullable();
            $table->string('url', 500)->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamp('creado_en')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materiales');
    }
};
