<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class PrasaranaModel extends Model
{
    use Notifiable;

    protected $table = 'ta_prasarana';
    protected $primaryKey = 'id';

    protected $fillable = [
        'ta','id_sek','nama','keterangan','panjang','lebar','kerusakan'
    ];
}
