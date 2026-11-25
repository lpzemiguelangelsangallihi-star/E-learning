<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->ulid('ulid')->nullable()->unique()->after('id');
        });

        Schema::table('notificaciones', function (Blueprint $table) {
            $table->ulid('ulid')->nullable()->unique()->after('id');
        });

        DB::table('users')->whereNull('ulid')->chunkById(500, function ($filas) {
            foreach ($filas as $fila) {
                DB::table('users')->where('id', $fila->id)->update(['ulid' => (string) Str::ulid()]);
            }
        });

        DB::table('notificaciones')->whereNull('ulid')->chunkById(500, function ($filas) {
            foreach ($filas as $fila) {
                DB::table('notificaciones')->where('id', $fila->id)->update(['ulid' => (string) Str::ulid()]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_ulid_unique');
            $table->dropColumn('ulid');
        });

        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropUnique('notificaciones_ulid_unique');
            $table->dropColumn('ulid');
        });
    }
};
