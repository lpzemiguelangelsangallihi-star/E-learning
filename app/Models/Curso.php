<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    protected $table = 'cursos';


    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';


    protected $fillable = [
        'materia_id',
        'profesor_id',
        'titulo',
        'descripcion',
        'objetivos',
        'imagen',
<<<<<<< HEAD
        'nivel',
        'duracion',
        'tipo',
        'precio',
=======
>>>>>>> d01924e12fe69647e775d4b6c8098de39255410a
        'estado',
    ];


    protected function casts(): array
    {
        return [
            'materia_id' => 'integer',
            'profesor_id' => 'integer',
<<<<<<< HEAD
            'duracion' => 'integer',
            'precio' => 'decimal:2',
=======
>>>>>>> d01924e12fe69647e775d4b6c8098de39255410a
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }


    public function materia(): BelongsTo
    {
        return $this->belongsTo(
            Materia::class,
            'materia_id'
        );
    }


    public function profesor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'profesor_id'
        );
    }


    public function inscripciones(): HasMany
    {
        return $this->hasMany(
            Inscripcion::class,
            'curso_id'
        );
    }
}