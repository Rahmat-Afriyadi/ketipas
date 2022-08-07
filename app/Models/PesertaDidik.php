<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class PesertaDidik extends Model
{
    use Notifiable;

    protected $table = 'ta_siswa';
    protected $primaryKey = 'id';

    protected $fillable = [
       'nama', 'nipd', 'jk','nisn','tmp_lhr','tgl_lhr','nik','agama','id_sek', 'ta', 'alamat', 'rt', 'rw', 'dusun', 'kelurahan', 'kecamatan', 'rombel',
       'hp', 'nm_ayah', 'nik_ayah', 'hp_ayah', 'nm_ibu', 'nik_ibu', 'sek_asal'
    ];
}
