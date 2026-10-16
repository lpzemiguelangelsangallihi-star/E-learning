<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class bitacoras extends Model
{
    protected $table = 'bitacoras';
    public const CREATED_AT = 'fecha';
    public const UPDATED_AT = null;
=======

class bitacoras extends Model
{
    //
    protected $table = 'bitacoras';
    public const CREATED_AT = 'fecha';
    public const UPDATE_AT = null;
>>>>>>> d01924e12fe69647e775d4b6c8098de39255410a
    protected $fillable = [
        'accion',
        'visible',
        'usuario_id',
    ];
    protected function casts(): array
    {
        return [
            'visible' => 'boolean',
        ];
    }
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
<<<<<<< HEAD
=======

>>>>>>> d01924e12fe69647e775d4b6c8098de39255410a
}
