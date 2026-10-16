<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioExperiencia extends Model
{
    protected $table = 'usuario_experiencia';

    public const CREATED_AT = null;

    public const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'estudiante_id',
        'experiencia_total',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'estudiante_id');
    }
}
