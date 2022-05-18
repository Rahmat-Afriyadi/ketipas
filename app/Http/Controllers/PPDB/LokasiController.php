<?php

namespace App\Http\Controllers\PPDB;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use View;
use Str;

class LokasiController extends Controller{

    static function GetLokasi($req){
        $id_inst = 1;
        $tahun  = DB::table('ref_tahun')->where('id',$req->id_thn)->value('tahun');
        $info  = DB::table('ta_ppdb_info')->where('id_inst',$id_inst)->where('status',1)->first();
        if($info){
            $idsek[] = 0;
            $dsek  = DB::table('ta_ppdb_sek')->where('id_inst',$id_inst)->where('id_thn',$req->id_thn)->get();
            foreach($dsek as $dts){
                $idsek[] = $dts->id_sek;
            }
            $no = 1;
            $data  = DB::table('ta_sekolah')->where('id_inst',$id_inst)->where('jenjang',$req->jenjang)->whereIn('id',$idsek)->get();
            foreach($data as $dat){
                $datas[]  = [
                  'id'  => $dat->id,
                  'urut'  => $no,
                  'nama'  => $dat->nama,
                  'email'  => $dat->email,
                  'alamat'  => $dat->alamat,
                ];
                $no++;
            }
            if(!sizeOf($data)) $datas = '';
            $nm_pel  = $info->nm_pel;
            $status  = true;
            $message = 'Sukses';
        }else{
            $nm_pel  = '';
            $status  = false;
            $message = 'Gagal';
            $datas = [];
        }
        return response()->json([
          'status'  => $status,
          'message' => $message,
          'data'  => $datas,
          'nm_pel'  => $nm_pel,
        ]);

    }



}
