<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materia extends Model
{
    protected $table = 'materias';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'materia_id');
    }
}
