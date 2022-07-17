<?php

namespace App\Imports;

use App\Models\PesertaDidik;
use Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Session;

class PDImport implements ToModel, WithStartRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function startRow(): int
    {
        return 7;
    }

    public function model(array $row)
    {
        $id = Auth::id();
        return new PesertaDidik([
            'ta'     => Session::get('ta'),
            'id_sek'     => Session::get('id_sek'),
            'nama'     => $row[1],
            'nipd'    => $row[2],
            'jk'    => $row[3],
            'nisn'    => $row[4],
            'tmp_lhr'    => $row[5],
            'tgl_lhr'    => $row[6],
            'nik'    => $row[7],
            'agama'    => $row[8],
            'alamat'  => $row[9],
            'rt'  => $row[10],
            'rw'  => $row[11],
            'dusun'  => $row[12],
            'kelurahan'  => $row[13],
            'kecamatan'  => $row[14],
            'rombel'  => $row[42],
        ]);
    }
}
