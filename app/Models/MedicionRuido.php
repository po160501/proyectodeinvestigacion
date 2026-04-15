<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicionRuido extends Model
{
    protected $table = 'mediciones_ruido';
    protected $fillable = ['sensor_id', 'decibeles', 'fecha', 'hora'];

    public function sensor()
    {
        return $this->belongsTo(Sensor::class, 'sensor_id');
    }
}
