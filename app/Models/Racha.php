<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Racha extends Model
{
    protected $table = 'rachas';

    public $timestamps = false;

    protected $fillable = [
        'estudiante_id',
        'racha_actual',
        'mejor_racha',
        'ultima_fecha',
    ];

    protected function casts(): array
    {
        return [
            'ultima_fecha' => 'date',
        ];
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'estudiante_id');
    }
}
