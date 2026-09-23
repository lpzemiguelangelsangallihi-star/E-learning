<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Insignia extends Model
{
    protected $table = 'insignias';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'nombre',
        'descripcion',
        'icono',
        'condicion',
    ];

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'usuario_insignias', 'insignia_id', 'estudiante_id')
            ->withPivot('fecha_obtencion');
    }
}
