<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EtagManual extends Model
{
    protected $table = 'etag_manual';
    protected $fillable = ['fecha', 'hora_evento', 'hora_alerta', 'nota', 'fuente'];
}
