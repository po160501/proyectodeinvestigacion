<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = ['obra_id', 'nombre'];

    public function obra()
    {
        return $this->belongsTo(Obra::class, 'obra_id');
    }

    public function trabajadores()
    {
        return $this->hasMany(Trabajador::class, 'area_id');
    }
}
