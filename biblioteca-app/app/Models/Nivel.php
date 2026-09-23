<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nivel extends Model
{
    protected $table = 'niveles';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'experiencia_minima',
        'experiencia_maxima',
        'descripcion',
    ];
}
