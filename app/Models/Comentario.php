<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comentario extends Model
{
    protected $table = 'comentarios';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'publicacion_id',
        'usuario_id',
        'comentario',
    ];

    public function publicacion(): BelongsTo
    {
        return $this->belongsTo(PublicacionForo::class, 'publicacion_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User:class, 'User_id');
    }
}
