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
    Schema::create('iop_modelos_decision', function (Blueprint $table) {
        $table->id();

        $table->string('nombre', 100);

        // Matriz de utilidades en formato JSON.
        $table->json('matriz_utilidades');

        $table->string('metodo', 30)->default('maximin');

        $table->unsignedInteger('version')->default(1);

        $table->boolean('activo')->default(true);

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iop_modelos_decision');
    }
};
