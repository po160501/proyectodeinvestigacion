<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TercManual extends Model {
    protected $table = 'terc_manual';
    protected $fillable = ['fecha','hora_inicio','hora_fin','decibeles','nota'];
}
