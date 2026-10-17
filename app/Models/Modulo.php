<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modulo extends Model
{
    protected $table = 'modulos';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'curso_id',
        'titulo',
        'descripcion',
        'orden',
        'estado',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    /** Temas de la unidad, en su orden. */
    public function lecciones(): HasMany
    {
        return $this->hasMany(Leccion::class, 'modulo_id')
            ->orderBy('orden')
            ->orderBy('id');
    }
}
