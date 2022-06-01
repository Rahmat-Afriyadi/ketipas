<?php

namespace App\Http\Controllers\Sekolah;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use View;
use Str;
use Image;
use File;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Config\ConfigController as Config;
use App\Http\Controllers\Admin\HAController as HA;

class SekolahController extends Controller{

    static function GetSekByKec($req){
        $success  = false; $message = 'Gagal Get Data';
        $user  = Auth::user();
        $otoritas  = HA::GetOtoritas(Auth::id(),1);
        $query  = $data  = DB::table('ta_sekolah');
        if($otoritas['admin']){
            if($req->jenjang != 0) $query->where('jenjang',$req->jenjang);  //tak usah dihapus
            if($req->id_kec != 0) $query->where('id_kec',$req->id_kec);
            if($req->jen_sek != 0) $query->where('jen_sek',$req->jen_sek);

        }elseif($otoritas['operator']){

        }else{
            return response()->json([
                'success'  => $success,
                'message' => $message,
                'otoritas'  => $otoritas,
                'data'  => [],
            ]);
        }

        if($req->data_vaksin == 1){
            if($req->data_vaksin != '0') $query->where('jenjang',$req->jenjang);  //tak usah dihapus
        }

        $data  = $query->get();
        $urut = 1;
        foreach($data as $dat){
            if($dat->status) $nm_status = 'Aktif';
            else $nm_status = 'Tidak Aktif';
            $opr   = '';
            $oprs  = DB::table('ta_sekolah_opr')->where('id_sek',$dat->id)->where('status',1)->get();
            foreach($oprs as $dopr){
                $opr .= $opr.' - '.$dopr->email;
            }
            $datas[]  = [
              'id'  => $dat->id,
              'nama'  => $dat->nama,
              'email'  => $dat->email,
              'alamat'  => $dat->alamat,
              'status'  => $dat->status,
              'id_kec'  => $dat->id_kec,
              'nm_status' => $nm_status,
              'urut'  => $urut,
              'operator'  => $opr
            ];
            $urut++;
        }
        if(!sizeOf($data)) $datas = [];
        else{
          $success = true; $message = 'Gagal';
        }




        return response()->json([
            'success'  => $success,
            'message' => $message,
            'otoritas'  => $otoritas,
            'data'  => $datas,
        ]);

    }

    static function GetSekByJenis($req){
        $success  = false; $message = 'Gagal Get Data';
        $otoritas  = HA::GetOtoritas(Auth::id(),1);
        if(!$otoritas['khusus']){
            return [
              'success'  => $success,
              'message' => $message,
              'data'  => [],
              'req' => $req->all(),
              'khusus'  => false,
            ];
        }
        $sekupdate  = DB::table('ta_sekolah')->select('sekolah_id','id')->get();
        foreach($sekupdate as $dsek){
            $cek  = DB::table('dapodik_sekolah')->where('sekolah_id',$dsek->sekolah_id)->first();
            if($cek){
                DB::table('ta_sekolah')->where('id',$dsek->id)->update([
                    'jen_sek' => $cek->status_sekolah,
                ]);
            }
        }

        $query  = DB::table('ta_sekolah')->select('jenjang')->groupBy('jenjang');
        if($req->id_kec != 0) $query->where('id_kec',$req->id_kec);
        if($req->jen_sek != 0) $query->where('jen_sek',$req->jen_sek);
        $sek  = $query->get();
        foreach($sek as $dat){
            $jenjang[]  = $dat->jenjang;
        }
        if(sizeOf($sek)){
            $sek  = DB::table('ref_jenjang_sek')->whereIn('uraian',$jenjang)->get();
            foreach($sek as $dat){
                $datas[]  = [
                  'id'  => $dat->id,
                  'uraian'  => $dat->uraian,
                ];
            }
            $success = true; $message = 'Sukses Get Data';
        }else{
            $datas  = [];
        }


        return response()->json([
            'success'  => $success,
            'message' => $message,
            'otoritas'  => $otoritas,
            'data'  => $datas,
        ]);
    }

    static function FilterSekolahSatu($req){
        // digunakan di :
        // 1. LapKelulusanComponent
        $success = false; $message = 'Gagal Filter Data';
        $query  = $data  = DB::table('ta_sekolah');
        if($req->jenjang != '0') $query->where('jenjang',$req->jenjang);
        if($req->id_kec != 0) $query->where('id_kec',$req->id_kec);
        if($req->jen_sek != 0) $query->where('jen_sek',$req->jen_sek);
        $data  = $query->get();
        $urut = 1;
        foreach($data as $dat){
            if($dat->status) $nm_status = 'Aktif';
            else $nm_status = 'Tidak Aktif';
            $opr   = '';
            $oprs  = DB::table('ta_sekolah_opr')->where('id_sek',$dat->id)->where('status',1)->get();
            foreach($oprs as $dopr){
                $opr .= $opr.' - '.$dopr->email;
            }
            $datas[]  = [
              'id'  => $dat->id,
              'nama'  => $dat->nama,
              'email'  => $dat->email,
              'alamat'  => $dat->alamat,
              'status'  => $dat->status,
              'id_kec'  => $dat->id_kec,
              'nm_status' => $nm_status,
              'urut'  => $urut,
              'operator'  => $opr
            ];
            $urut++;
        }
        if(!sizeOf($data)) $datas = [];
        $success = true;
        $otoritas  = HA::GetOtoritas(Auth::id(),1);
        return response()->json([
            'success'  => $success,
            'message' => $message,
            'data'  => $datas,
            'dat'  => $data,
            'otoritas'  => $otoritas,
            'req' => $req->all(),
        ]);

    }


}
