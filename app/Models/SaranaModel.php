<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class SaranaModel extends Model
{
    use Notifiable;

    protected $table = 'ta_sarana';
    protected $primaryKey = 'id';

    protected $fillable = [
        'ta','id_sek','jenis','letak','pemilik','spek','jml','laik','tdk_laik'
    ];
}
