<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
<<<<<<< HEAD
    /**
     * Run the migrations.
     */
=======
>>>>>>> d01924e12fe69647e775d4b6c8098de39255410a
    public function up(): void
    {
        Schema::create('bitacoras', function (Blueprint $table) {
            $table->id();
            $table->string('accion', 255);
            $table->timestamp('fecha')->useCurrent();
            $table->boolean('visible')->default(true);
<<<<<<< HEAD
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
=======
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
>>>>>>> d01924e12fe69647e775d4b6c8098de39255410a
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacoras');
    }
};