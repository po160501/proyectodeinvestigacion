<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Obra extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'limite_db'];

    public function trabajadores()
    {
        return $this->hasMany(Trabajador::class, 'obra_id');
    }

    public function areas()
    {
        return $this->hasMany(Area::class, 'obra_id');
    }

    public function generarToken(): string
    {
        return \Illuminate\Support\Str::random(32);
    }
}
