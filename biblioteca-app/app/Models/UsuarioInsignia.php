<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioInsignia extends Model
{
    protected $table = 'usuario_insignias';

    public $timestamps = false;

    protected $fillable = [
        'estudiante_id',
        'insignia_id',
        'fecha_obtencion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_obtencion' => 'datetime',
        ];
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'estudiante_id');
    }

    public function insignia(): BelongsTo
    {
        return $this->belongsTo(Insignia::class, 'insignia_id');
    }
}
