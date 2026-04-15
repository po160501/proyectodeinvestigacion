<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExposicionRuido extends Model
{
    protected $table = 'exposicion_ruido';
    protected $fillable = ['trabajador_id', 'hora_inicio', 'hora_fin', 'tiempo_exposicion', 'decibeles', 'fecha'];

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'trabajador_id');
    }
}
