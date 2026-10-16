<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Leccion extends Model
{
    protected $table = 'lecciones';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'modulo_id',
        'titulo',
        'descripcion',
        'contenido',
        'orden',
        'estado',
    ];

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class, 'modulo_id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class, 'leccion_id');
    }

    public function materiales(): HasMany
    {
        return $this->hasMany(Material::class, 'leccion_id');
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'leccion_id');
    }

    public function examenes(): HasMany
    {
        return $this->hasMany(Examen::class, 'leccion_id');
    }

    public function progresos(): HasMany
    {
        return $this->hasMany(ProgresoLeccion::class, 'leccion_id');
    }
}
