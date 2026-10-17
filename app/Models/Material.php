<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Material extends Model
{
    protected $table = 'materiales';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'leccion_id',
        'titulo',
        'tipo',
        'archivo',
        'url',
        'descripcion',
    ];

    public function leccion(): BelongsTo
    {
        return $this->belongsTo(Leccion::class, 'leccion_id');
    }

    /** URL para abrir/descargar el material (archivo subido o enlace). */
    public function getEnlaceAttribute(): ?string
    {
        if ($this->archivo) {
            return asset('storage/'.$this->archivo);
        }

        return $this->url;
    }

    public function getIconoAttribute(): string
    {
        return match ($this->tipo) {
            'pdf' => 'fa-solid fa-file-pdf',
            'imagen' => 'fa-solid fa-file-image',
            'presentacion' => 'fa-solid fa-file-powerpoint',
            'documento' => 'fa-solid fa-file-lines',
            'enlace' => 'fa-solid fa-link',
            default => 'fa-solid fa-file',
        };
    }
}
