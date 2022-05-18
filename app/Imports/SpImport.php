<?php

namespace App\Imports;

use App\Models\SaranaModel;
use Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Session;

class SpImport implements ToModel, WithStartRow
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
        return new SaranaModel([
            'ta'     => Session::get('ta'),
            'id_sek'     => Session::get('id_sek'),
            'jenis'     => $row[1],
            'letak'    => $row[2],
            'pemilik'    => $row[3],
            'spek'    => $row[4],
            'jml'    => $row[5],
            'laik'    => $row[6],
            'tdk_laik'    => $row[7],
        ]);
    }
}
