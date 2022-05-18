<?php

namespace App\Imports;

use App\Models\NonTendikModel;
use Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Session;

class NonTendikImport implements ToModel, WithStartRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function startRow(): int
    {
        return 6;
    }

    public function model(array $row)
    {
        $id = Auth::id();
        $id_sek  = DB::table('ta_sekolah_opr')->where('id_user',$id)->where('status',1)->value('id_sek');
        return new NonTendikModel([
            'ta'     => Session::get('ta'),
            'id_sek'     => $id_sek, //Session::get('id_sek'),
            'nama'     => $row[1],
            'nik'    => $row[44],
            'nuptk'    => $row[2],
            'jk'    => $row[3],
            'tmp_lhr'    => $row[4],
            'tgl_lhr'    => $row[5],
            'nip'    => $row[6],
            'status_peg'    => $row[7],
            'jenis_ptk'    => $row[8],
            'agama'    => $row[9],
            'alamat'    => $row[10],
            'hp'    => $row[18],
            'email'    => $row[19],
        ]);
    }
}
