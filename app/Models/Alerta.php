<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    protected $table = 'alertas';
    protected $fillable = ['sensor_id', 'nivel_ruido', 'fecha', 'hora', 'estado', 'trabajador_id', 'obra_id'];

    public function sensor()
    {
        return $this->belongsTo(Sensor::class, 'sensor_id');
    }

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'trabajador_id');
    }

    public function obra()
    {
        return $this->belongsTo(Obra::class, 'obra_id');
    }
}
