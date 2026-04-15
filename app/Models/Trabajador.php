<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trabajador extends Model
{
    protected $table = 'trabajadores';
    protected $fillable = ['nombre', 'empresa', 'area'];

    public function exposiciones()
    {
        return $this->hasMany(ExposicionRuido::class, 'trabajador_id');
    }
}
