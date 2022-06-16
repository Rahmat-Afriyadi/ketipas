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


    static function GetDataAllSekolah($id_kec){
        $id_user  = Auth::id(); $success = false; $message = '';
        $opr   = DB::table('ta_instansi_opr')->where('id_user',$id_user)->where('status',1)->get();
        if(sizeOf($opr)){
            foreach($opr as $dtopr){
                $idinst[]  = $dtopr->id_inst;
            }
            $data  = DB::table('ta_sekolah')->whereIn('id_inst',$idinst)->get();
            foreach($data as $dat){
                if($dat->status) $nm_status = 'Aktif';
                else $nm_status = 'Tidak Aktif';
                $datas[] = [
                  'id'  => $dat->id,
                  'nama'  => $dat->nama,
                  'email' => $dat->email,
                  'alamat'  => $dat->alamat,
                  'status'  => $dat->status,
                  'id_kec'  => $dat->id_kec,
                  'nm_status' => $nm_status
                ];
            }
            if(!sizeOf($data)) $datas  = '';
            $otoritas = 1; $success = true;
        }else{
            $otoritas = 0; $success = false;
            $datas = '';
        }
        return response()->json([
            'success'  => $success,
            'message' => $message,
            'otoritas'=>$otoritas,
            'data'=>$datas,
            'admin' => DB::table('users')->where('id',Auth::id())->where('status',1)->value('admin')
        ]);

    }

    static function GuestGetSekolahPPDB($req){
        $success = false; $message = ''; $datas = [];
        $nm_pel  = DB::table('ta_ppdb_info')->where('id',1)->value('nm_pel');

        $info  = DB::table('ta_ppdb_sek')->where('id_thn',$req->id_thn)->where('status',1)->get();
        foreach($info as $dinf){
            $idsek[] = 0;
            $dsek  = DB::table('ta_ppdb_kuota')->where('id_ppdb_sek',$dinf->id)->get();
            foreach($dsek as $dts){
                $oke = 0;
                if($dts->zonasi) $oke = 1;
                elseif($dts->afirmasi) $oke = 1;
                elseif($dts->prestasi) $oke = 1;
                elseif($dts->perpindahan) $oke = 1;
                if($oke) $idsek[] = $dinf->id_sek;
            }
        }
        if(sizeOf($info)){
            $no = 1;
            $data  = DB::table('ta_sekolah')->where('jenjang',$req->id_jenjang)->where('id_inst',1)->whereIn('id',$idsek)->get();
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
            if(!sizeOf($data)) $datas = [];
            else $success = true;
        }else{
            $nm_pel = 'infot tidak Ditemukan';
        }


        return response()->json([
            'success'  => $success,
            'message' => $message,
            'nm_inst'=>$nm_pel,
            'data'=>$datas,
            'req' => $req->all()
        ]);


    }

}
