<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use App\Models\Concerns\ResuelveBindingsEspanol;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modulo extends Model
{
    use HasPublicUlid;
    use ResuelveBindingsEspanol;

    protected $table = 'modulos';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'curso_id',
        'titulo',
        'descripcion',
        'orden',
        'estado',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    /**
     * @return array<string, string>
     */
    protected function relacionesDeRuta(): array
    {
        return ['leccion' => 'lecciones'];
    }

    /** Temas de la unidad, en su orden. */
    public function lecciones(): HasMany
    {
        return $this->hasMany(Leccion::class, 'modulo_id')
            ->orderBy('orden')
            ->orderBy('id');
    }
}
