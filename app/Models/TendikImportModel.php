<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TendikImportModel extends Model
{
    use Notifiable;

    protected $table = 'ta_tendik';
    protected $primaryKey = 'id';

    protected $fillable = [
       'ta', 'id_sek', 'nama', 'nik', 'nuptk','jk','tmp_lhr','tgl_lhr','nip','status_peg', 'jenis_ptk','agama','alamat', 'hp', 'email',
    ];
}
