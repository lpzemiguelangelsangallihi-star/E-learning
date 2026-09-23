<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'rol_id',
        'nombre',
        'apellido',
        'correo',
        'contrasena',
        'foto_perfil',
        'estado',
    ];

    protected $hidden = [
        'contrasena',
    ];

    protected function casts(): array
    {
        return [
            'contrasena' => 'hashed',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->contrasena;
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function cursosImpartidos(): HasMany
    {
        return $this->hasMany(Curso::class, 'profesor_id');
    }

    public function cursosInscritos(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'inscripciones', 'estudiante_id', 'curso_id')
            ->withPivot('estado', 'fecha_inscripcion');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'estudiante_id');
    }

    public function tareasCreadas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'profesor_id');
    }

    public function examenesCreados(): HasMany
    {
        return $this->hasMany(Examen::class, 'profesor_id');
    }

    public function entregas(): HasMany
    {
        return $this->hasMany(Entrega::class, 'estudiante_id');
    }

    public function intentosExamen(): HasMany
    {
        return $this->hasMany(IntentoExamen::class, 'estudiante_id');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'estudiante_id');
    }

    public function calificacionesOtorgadas(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'profesor_id');
    }

    public function progresoCursos(): HasMany
    {
        return $this->hasMany(ProgresoCurso::class, 'estudiante_id');
    }

    public function progresoLecciones(): HasMany
    {
        return $this->hasMany(ProgresoLeccion::class, 'estudiante_id');
    }

    public function publicacionesForo(): HasMany
    {
        return $this->hasMany(PublicacionForo::class, 'usuario_id');
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(Comentario::class, 'usuario_id');
    }

    public function mensajesEnviados(): HasMany
    {
        return $this->hasMany(Mensaje::class, 'remitente_id');
    }

    public function mensajesRecibidos(): HasMany
    {
        return $this->hasMany(Mensaje::class, 'destinatario_id');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'usuario_id');
    }

    public function racha(): HasOne
    {
        return $this->hasOne(Racha::class, 'estudiante_id');
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
}
