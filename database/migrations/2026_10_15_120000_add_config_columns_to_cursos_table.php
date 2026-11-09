<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->enum('nivel', ['basico', 'intermedio', 'avanzado'])
                ->default('basico')
                ->after('objetivos');

            // Duración estimada en horas.
            $table->unsignedInteger('duracion')
                ->default(0)
                ->after('nivel');

            $table->enum('tipo', ['gratuito', 'pago'])
                ->default('gratuito')
                ->after('duracion');

            $table->decimal('precio', 8, 2)
                ->default(0)
                ->after('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn(['nivel', 'duracion', 'tipo', 'precio']);
        });
    }
};
