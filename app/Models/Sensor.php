<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    protected $table = 'sensores';
    protected $fillable = ['nombre', 'ubicacion', 'estado', 'nivel_actual'];

    public function mediciones()
    {
        return $this->hasMany(MedicionRuido::class, 'sensor_id');
    }

    public function alertas()
    {
        return $this->hasMany(Alerta::class, 'sensor_id');
    }
}
