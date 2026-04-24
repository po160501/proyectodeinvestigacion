<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PdrManual extends Model {
    protected $table = 'pdr_manual';
    protected $fillable = ['fecha','hora','patron_db','iot_db','nota'];
}
