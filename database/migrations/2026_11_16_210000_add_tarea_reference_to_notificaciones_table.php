<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->integer('tarea_id')->nullable()->after('usuario_id');
            $table->unique(['usuario_id', 'tarea_id', 'tipo'], 'uk_notif_usuario_tarea_tipo');
        });
    }

    public function down(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropUnique('uk_notif_usuario_tarea_tipo');
            $table->dropColumn('tarea_id');
        });
    }
};
