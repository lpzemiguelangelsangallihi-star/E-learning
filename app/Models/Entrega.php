<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entrega extends Model
{
    protected $table = 'entregas';

    public $timestamps = false;

    protected $fillable = [
        'tarea_id',
        'estudiante_id',
        'archivo',
        'respuesta',
        'fecha_entrega',
        'estado',
        'calificacion',
        'retroalimentacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_entrega' => 'datetime',
            'calificacion' => 'decimal:2',
        ];
    }

    public function tarea(): BelongsTo
    {
        return $this->belongsTo(Tarea::class, 'tarea_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }
}
