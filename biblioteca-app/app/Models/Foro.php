<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Foro extends Model
{
    protected $table = 'foros';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'curso_id',
        'titulo',
        'descripcion',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function publicaciones(): HasMany
    {
        return $this->hasMany(PublicacionForo::class, 'foro_id');
    }
}
