<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trabajador extends Model
{
    protected $table = 'trabajadores';
    protected $fillable = ['nombre', 'empresa', 'area', 'obra_id', 'telefono', 'token_sesion', 'jornada_inicio', 'jornada_fin'];

    public function exposiciones()
    {
        return $this->hasMany(ExposicionRuido::class, 'trabajador_id');
    }

    public function obra()
    {
        return $this->belongsTo(Obra::class, 'obra_id');
    }
}
