<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IopEjercicioRefuerzo extends Model
{
    protected $table = 'iop_ejercicios_refuerzo';

    protected $fillable = [
        'leccion_id',
        'titulo',
        'descripcion',
        'tipo',
        'instrucciones',
        'url_recurso',
        'orden',
        'activo',
    ];

    protected $casts = [
        'leccion_id' => 'integer',
        'orden' => 'integer',
        'activo' => 'boolean',
    ];

    public function leccion(): BelongsTo
    {
        return $this->belongsTo(
            Leccion::class,
            'leccion_id'
        );
    }
    public function asignaciones(): HasMany
    {
        return $this->hasMany(
            IopAsignacionRefuerzo::class,
            'ejercicio_id'
            );
    }
}
