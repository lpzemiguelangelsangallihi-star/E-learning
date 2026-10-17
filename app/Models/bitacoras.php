<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class bitacoras extends Model
{
    //
    protected $table = 'bitacoras';
    public const CREATED_AT = 'fecha';
    public const UPDATED_AT = null;
    protected $fillable = [
        'accion',
        'visible',
        'usuario_id',
    ];
    protected function casts(): array
    {
        return [
            'visible' => 'boolean',
            'fecha' => 'datetime',
        ];
    }
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

}
