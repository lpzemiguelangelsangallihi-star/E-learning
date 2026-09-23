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
        Schema::create('videos', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('leccion_id');
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->string('url', 500);
            $table->integer('duracion')->default(0);
            $table->integer('orden')->default(1);
            $table->timestamp('creado_en')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
