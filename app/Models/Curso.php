<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Curso extends Model
{
    protected $table = 'cursos';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'categoria_id',
        'materia_id',
        'profesor_id',
        'titulo',
        'descripcion',
        'objetivos',
        'imagen',
        'nivel',
        'duracion',
        'tipo',
        'precio',
        'estado',
    ];

    protected function casts(): array
{
    return [
        'precio' => 'decimal:2',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];
}

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(User:: class, 'profesor_id');
    }

    public function modulos(): HasMany
    {
        return $this->hasMany(Modulo::class, 'curso_id');
    }

    public function lecciones(): HasManyThrough
    {
        return $this->hasManyThrough(Leccion::class, Modulo::class, 'curso_id', 'modulo_id');
    }

    public function foros(): HasMany
    {
        return $this->hasMany(Foro::class, 'curso_id');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'curso_id');
    }

    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'inscripciones', 'curso_id', 'estudiante_id')
            ->withPivot('estado', 'fecha_inscripcion');
    }

    public function progresos(): HasMany
    {
        return $this->hasMany(ProgresoCurso::class, 'curso_id');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'curso_id');
    }
}
