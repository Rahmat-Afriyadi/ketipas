<?php

namespace App\Http\Controllers\PPDB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Berita;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Config\ConfigController as Config;

class PPDBController extends Controller
{
    static function GetJadwal($req){
        $status = true; $message = 'Success Get GetJadwal';
        $id_inst = 1;
        $info   = DB::table('ta_ppdb_info')->where('status',1)->where('id_inst',$id_inst)->first();
        if($info){
            $data  = DB::table('ta_ppdb_jadwal')->where('id_thn',$req->id_thn)->where('id_info',$info->id)->orderBy('ref_jadwal')->get();
            $no = 1;
            foreach($data as $dat){
                $awal   = Config::getFormatHari($dat->awal).', '.Config::chFormatTanggal($dat->awal);
                $akhir   = Config::getFormatHari($dat->akhir).', '.Config::chFormatTanggal($dat->akhir);
                $datas[]  = [
                  'id'  => $dat->id,
                  'urut'  => $no,
                  'uraian'  => $dat->uraian,
                  'awal'  => $awal,
                  'akhir'  => $akhir,
                ];
                $no++;
            }
            if(!sizeOf($data)) $datas = '';
            $nm_pel = $info->nm_pel;
            $status = true;
            $message = 'Data Ditemukan';
        }else{
            $datas  = '';
            $nm_pel = 'Instansi Tidak Ditemukan';
            $status = false;
            $message = 'Data Tidak Ditemukan';
        }
        return response()->json([
          'status'  => $status,
          'message' => $message,
          'data'  => $datas,
          'nm_pel'  => $nm_pel,
        ]);
    }

}
