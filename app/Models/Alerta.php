<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    protected $table = 'alertas';
    protected $fillable = ['sensor_id', 'nivel_ruido', 'fecha', 'hora', 'estado'];

    public function sensor()
    {
        return $this->belongsTo(Sensor::class, 'sensor_id');
    }
}
