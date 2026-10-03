<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    protected $table = 'roles';


    public const CREATED_AT = null;
    public const UPDATED_AT = null;


    protected $fillable = [
        'nombre',
        'descripcion',
    ];


    public function usuarios(): HasMany
    {
        return $this->hasMany(
            User::class,
            'rol_id'
        );
    }
}