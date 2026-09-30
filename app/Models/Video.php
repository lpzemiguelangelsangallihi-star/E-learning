<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    protected $table = 'videos';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'leccion_id',
        'titulo',
        'descripcion',
        'url',
        'duracion',
        'orden',
    ];

    public function leccion(): BelongsTo
    {
        return $this->belongsTo(Leccion::class, 'leccion_id');
    }
}
