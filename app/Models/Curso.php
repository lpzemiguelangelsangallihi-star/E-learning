<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Curso extends Model
{
    protected $table = 'cursos';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    private const ALFABETO = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    protected $fillable = [
        'materia_id',
        'profesor_id',
        'titulo',
        'codigo_clase',
        'descripcion',
        'objetivos',
        'imagen',
        'nivel',
        'duracion',
        'tipo',
        'precio',
        'estado',
    ];

    protected static function booted(): void
    {
        // Todo curso nuevo recibe automáticamente su código de clase.
        static::creating(function (Curso $curso) {
            if (empty($curso->codigo_clase)) {
                $curso->codigo_clase = static::generarCodigo();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'materia_id' => 'integer',
            'profesor_id' => 'integer',
            'duracion' => 'integer',
            'precio' => 'decimal:2',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    /**
     * Código único de 8 caracteres (sin 0/O/1/I para evitar confusiones).
     */
    public static function generarCodigo(): string
    {
        do {
            $codigo = '';

            for ($i = 0; $i < 8; $i++) {
                $codigo .= self::ALFABETO[random_int(0, strlen(self::ALFABETO) - 1)];
            }
        } while (static::where('codigo_clase', $codigo)->exists());

        return $codigo;
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'curso_id');
    }

    /** Unidades del curso, en su orden. */
    public function modulos(): HasMany
    {
        return $this->hasMany(Modulo::class, 'curso_id')
            ->orderBy('orden')
            ->orderBy('id');
    }

    /** Todos los temas del curso (a través de sus unidades). */
    public function lecciones(): HasManyThrough
    {
        return $this->hasManyThrough(
            Leccion::class,
            Modulo::class,
            'curso_id',
            'modulo_id'
        );
    }
}
