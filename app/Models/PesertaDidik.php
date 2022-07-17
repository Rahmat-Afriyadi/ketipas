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
       'nama', 'nipd', 'jk','nisn','tmp_lhr','tgl_lhr','nik','agama','id_sek', 'ta', 'alamat', 'rt', 'rw', 'dusun', 'kelurahan', 'kecamatan', 'rombel'
    ];
}


// ALTER TABLE `ta_siswa` ADD `rombel` VARCHAR(25) NULL AFTER `agama`;

// ALTER TABLE `ta_siswa` ADD `alamat` VARCHAR(255) NULL AFTER `agama`, ADD `rt` VARCHAR(5) NULL AFTER `alamat`, ADD `rw` VARCHAR(5) NULL AFTER `rt`, ADD `dusun` VARCHAR(255) NULL AFTER `rw`, ADD `kelurahan` VARCHAR(255) NULL AFTER `dusun`, ADD `kecamatan` VARCHAR(255) NULL AFTER `kelurahan`;
