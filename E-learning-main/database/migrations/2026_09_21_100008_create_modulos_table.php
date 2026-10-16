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
        Schema::create('modulos', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('curso_id');
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->integer('orden')->default(1);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamp('creado_en')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modulos');
    }
};
