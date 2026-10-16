<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgresoLeccion extends Model
{
    protected $table = 'progreso_lecciones';

    public $timestamps = false;

    protected $fillable = [
        'leccion_id',
        'estudiante_id',
        'porcentaje',
        'completado',
        'fecha_inicio',
        'fecha_completado',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje' => 'decimal:2',
            'completado' => 'boolean',
            'fecha_inicio' => 'datetime',
            'fecha_completado' => 'datetime',
        ];
    }

    public function leccion(): BelongsTo
    {
        return $this->belongsTo(Leccion::class, 'leccion_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }
}
