<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscripcion extends Model
{
    protected $table = 'inscripciones';

    public $timestamps = false;

    protected $fillable = [
        'curso_id',
        'estudiante_id',
        'fecha_inscripcion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inscripcion' => 'datetime',
        ];
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'estudiante_id');
    }
}
