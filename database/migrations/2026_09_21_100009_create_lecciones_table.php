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
        Schema::create('lecciones', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('modulo_id');
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->text('contenido')->nullable();
            $table->integer('orden')->default(1);
            $table->enum('estado', ['borrador', 'publicado', 'oculto'])->default('borrador');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecciones');
    }
};
