<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Examen extends Model
{
    public const IOP_NINGUNO = 'ninguno';

    public const IOP_DIAGNOSTICO = 'diagnostico';

    public const IOP_REFUERZO = 'refuerzo';

    public const IOP_ORDINARIA = 'ordinaria';

    public const TIPOS_IOP = [
        self::IOP_NINGUNO => 'Sin IOP',
        self::IOP_DIAGNOSTICO => 'Diagnóstico (inicia el seguimiento IOP)',
        self::IOP_REFUERZO => 'Evaluación de refuerzo (registra avance IOP)',
        self::IOP_ORDINARIA => 'Evaluación ordinaria (no afecta a IOP)',
    ];

    protected $table = 'examenes';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'leccion_id',
        'profesor_id',
        'titulo',
        'descripcion',
        'tiempo_limite',
        'intentos_permitidos',
        'puntaje_maximo',
        'mostrar_correctas',
        'tipo_iop',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'puntaje_maximo' => 'decimal:2',
            'mostrar_correctas' => 'boolean',
        ];
    }

    public function leccion(): BelongsTo
    {
        return $this->belongsTo(Leccion::class, 'leccion_id');
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    public function preguntas(): HasMany
    {
        return $this->hasMany(Pregunta::class, 'examen_id');
    }

    public function intentos(): HasMany
    {
        return $this->hasMany(IntentoExamen::class, 'examen_id');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'examen_id');
    }
}
