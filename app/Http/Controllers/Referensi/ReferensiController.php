<?php

namespace App\Http\Controllers\Referensi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Berita;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReferensiController extends Controller
{
    static function GetTahun(){
        $status = true; $message = 'Success Get Tahun';
        $data  = DB::table('ref_tahun')->get();
        foreach($data as $dat){
            $datas[]  = [
              'id'    => $dat->id,
              'tahun'   => $dat->tahun,
              'tahun_ajaran'  => $dat->tahun_ajaran,
            ];
        }
        if(!sizeOf($data)){
            $datas  = [];
            $status = false; $message = 'Gagal Get Tahun';
        }
        return response()->json([
          'status'  => $status,
          'message' => $message,
          'data'  => $datas
        ]);
    }

    static function GetKecamatan(){
        $status = true; $message = 'Success Get Tahun';
        $data  = DB::table('ref_kecamatan')->where('status',1)->get();
        foreach($data as $dat){
            $datas[]  = [
              'id'    => $dat->id,
              'kode_wilayah'   => $dat->kode_wilayah,
              'uraian'  => $dat->uraian,
            ];
        }
        if(!sizeOf($data)){
            $datas  = [];
            $status = false; $message = 'Gagal Get Tahun';
        }
        return response()->json([
          'status'  => $status,
          'message' => $message,
          'data'  => $datas
        ]);
    }

}
