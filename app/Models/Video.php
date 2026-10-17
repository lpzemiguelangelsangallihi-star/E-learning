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

    /**
     * Un video es "local" cuando el profesor subió el archivo
     * (en `url` se guarda la ruta dentro del disco public).
     */
    public function getEsLocalAttribute(): bool
    {
        return ! preg_match('#^https?://#i', (string) $this->url);
    }

    /** URL del archivo local servido desde /storage. */
    public function getArchivoUrlAttribute(): ?string
    {
        return $this->es_local ? asset('storage/'.$this->url) : null;
    }

    /**
     * URL para incrustar (iframe) si es YouTube o Vimeo; null en otro caso.
     */
    public function getEmbedUrlAttribute(): ?string
    {
        if ($this->es_local) {
            return null;
        }

        $url = (string) $this->url;

        if (preg_match(
            '#(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})#i',
            $url,
            $m
        )) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('#vimeo\.com/(?:video/)?(\d+)#i', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return null;
    }

    /** Enlace directo a un .mp4/.webm externo (para usar con <video>). */
    public function getEsArchivoDirectoAttribute(): bool
    {
        return ! $this->es_local
            && preg_match('#\.(mp4|webm|ogg|mov)(\?.*)?$#i', (string) $this->url) === 1;
    }
}
