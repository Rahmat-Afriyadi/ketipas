<?php

namespace App\Imports;

use App\Models\PrasaranaModel;
use Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Session;

class PrImport implements ToModel, WithStartRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function startRow(): int
    {
        return 8;
    }

    public function model(array $row)
    {
        $id = Auth::id();
        return new PrasaranaModel([
            'ta'     => Session::get('ta'),
            'id_sek'     => Session::get('id_sek'),
            'nama'     => $row[1],
            'keterangan'    => $row[2],
            'panjang'    => $row[3],
            'lebar'    => $row[4],
            'kerusakan'    => $row[27],
        ]);
    }
}
