<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class bitacoras extends Model
{
    //
    protected $table = 'bitacoras';
    public const CREATED_AT = 'fecha';
    public const UPDATE_AT = null;
    protected $fillable = [
        'accion',
        'visible',
        'usuario_id',
    ];
    protected function casts(): array
    {
        return [
            'visible' => 'boolean',
        ];
    }
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

}
