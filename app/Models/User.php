<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'rol_id',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'password',
        'estado',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
        'nombre_completo',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(
            Rol::class,
            'rol_id'
        );
    }

    public function experiencia(): HasOne
    {
        return $this->hasOne(UsuarioExperiencia::class, 'estudiante_id');
    }

    public function insignias(): BelongsToMany
    {
        return $this->belongsToMany(Insignia::class, 'usuario_insignias', 'estudiante_id', 'insignia_id')
            ->withPivot('fecha_obtencion');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'usuario_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim(
            "{$this->nombre} "
            ."{$this->apellido_paterno} "
            ."{$this->apellido_materno}"
        );
    }
}
