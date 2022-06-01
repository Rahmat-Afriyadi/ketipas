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
use Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PDImport;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Config\ConfigController as Config;
use App\Http\Controllers\Admin\HAController as HA;

class PesertaDidikController extends Controller{
    private static $tahun = 2021;
    static function GetByTA($req){
        $id_user  = Auth::ID(); $success = false; $sekolah = ''; $import = false;
        $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
        if($opr){
            $success = true;
            Session::put('id_sek', $opr->id_sek);
            $ta  = DB::table('ref_tahun')->where('id',$req->ta)->first();
            Session::put('ta', $ta->tahun);
            $data  = DB::table('ta_siswa')->where('id_sek',$opr->id_sek)->where('ta',$ta->tahun)->get();
            $sekolah = DB::table('ta_sekolah')->select('id','id_kec','nama','email')->where('id',$opr->id_sek)->first();
            if($ta->tahun == static::$tahun){
                $import = true;
            }

        }else{
            Session::put('id_sek', 0);
            Session::put('ta', 0);
            $data  = [];
            $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
        }

        return response()->json([
            'message' => '',
            'success' => $success,
            'req'  => $req->all(),
            'data'  => $data,
            'opr' => $opr,
            'otoritas'  => $success,
            'import'  => $import,
            'sekolah'  => $sekolah,
            'session' => Session::get('id_sek').' dan '.Session::get('ta'),
            'id_sek'  => Session::get('id_sek'),
            'ta'  => Session::get('ta'),
        ]);
    }

    static function GetPesertaDidik($req){
        $success = false; $message = 'Gagal Get Data'; $data = [];
        $id_user  = Auth::ID();
        $admin  = DB::table('users')->where('id',$id_user)->where('admin',1)->where('status',1)->count();
        if(!$admin){
            $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
            if($opr){
                  $sek    = DB::table('ta_sekolah')->select('id','id_kec')->where('id',$opr->id_sek);
                  $data  = $sek->get();
                  foreach($data as $dat){
                      $id_sek[] = $dat->id;
                  }
                  if(!sizeOf($data)) $id_sek[] = 0;
            }else{
                $id_sek[]  = 0;
            }
        }else{
              $sek    = DB::table('ta_sekolah')->select('id','id_kec');
              if($req->id_kec) $sek->where('id_kec',$req->id_kec);
              if($req->id_sek) $sek->where('id',$req->id_sek);
              if($req->jenjang) $sek->where('jenjang',$req->jenjang);
              $data  = $sek->get();
              foreach($data as $dat){
                  $id_sek[] = $dat->id;
              }
              if(!sizeOf($data)) $id_sek[] = 0;
        }

        $data  = DB::table('ta_siswa')->whereIn('id_sek',$id_sek)->where('ta',$req->thn_ajar)->get();

        $success = true;
        return response()->json([
          'success' => $success,
          'message' => $message,
          'data'  => $data,
        ]);
    }

    static function ImportData($req){
        $id_user  = Auth::ID();
        $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();

        Session::put('id_sek', $opr->id_sek);
        Session::put('ta', static::$tahun);
        $id_sek  = Session::get('id_sek');
        $ta  = Session::get('ta');
        DB::table('ta_siswa')->where('id_sek',$id_sek)->where('ta',$ta)->delete();
        Excel::import(new PDImport,request()->file('file'));
        $data  = DB::table('ta_siswa')->where('ta',$ta)->where('id_sek',$id_sek)->get();

        return response()->json([
          'success' => true,
          'message' => 'Import Data Berhasil',
          'data'  => $data,
          'req' => $req->all(),
          'session' => Session::get('id_sek').' dan '.Session::get('ta'),
        ]);
    }

    static function GetKelulusan($req){
        if(\Request::isMethod('POST')){
              $title  = '';
              $opr   = DB::table('ta_sekolah_opr')->where('id_user',Auth::id())->where('status',1)->first();
              if(!$opr){
                  return [
                    'success' => false,
                    'message' => 'Otoritas Operator Belum Disetting, Hubungi Administrator',
                    'otoritas'  => ['lihat'=>0],
                    'title' => 'Otoritas Operator Belum Disetting, Hubungi Administrator'
                  ];
              }
              $thn     = DB::table('ref_tahun')->where('id',$req->ta)->first();
              $sek     = DB::table('ta_sekolah')->where('id',$opr->id_sek)->first();
              $sek_dik = DB::table('dapodik_siswa_akhir')->where('sekolah_id',$sek->sekolah_id)->where('ta',$thn->tahun)->get();
              foreach($sek_dik as $dat){
                  $cek = DB::table('ta_kelulusan')->where('tahun',$thn->tahun)->where('id_sek',$sek->id)->where('nisn',$dat->nisn)->first();
                  if(!$cek){
                      $alamat  = $dat->alamat_jalan;
                      if($dat->rt) $alamat .= ' RT: '.$dat->rt;
                      if($dat->rw) $alamat .= ' RW: '.$dat->rw;
                      DB::table('ta_kelulusan')->insert([
                          'tahun' => $thn->tahun,
                          'id_sek'  => $sek->id,
                          'nisn'  => $dat->nisn,
                          'nama'  => $dat->nama,
                          'nik'  => $dat->nik,
                          'tempat_lahir'  => $dat->tempat_lahir,
                          'tanggal_lahir'  => $dat->tanggal_lahir,
                          'jenis_kelamin'  => $dat->jenis_kelamin,
                          'alamat'  => $alamat,
                      ]);
                  }
              }

              if(!sizeOf($sek_dik)){
                  $title  .= $sek->nama.' (Data Dapodik Tidak Tersedia)';
              }else{
                  $title  .= $sek->nama;
              }
              $urut = 1;
              $data    = DB::table('ta_kelulusan')->where('tahun',$thn->tahun)->where('id_sek',$opr->id_sek)->orderBy('nama')->get();
              foreach($data as $dat){
                  $datas[] = [
                    'id'  => $dat->id,
                    'urut'  => $urut,
                    'nisn'  => $dat->nisn,
                    'nama'  => $dat->nama,
                    'nik'  => $dat->nik,
                    'ttl'  => $dat->tempat_lahir.' / '.$dat->tanggal_lahir,
                    'jenis_kelamin'  => $dat->jenis_kelamin,
                    'alamat'  => $dat->alamat,
                    'is_lulus'  => $dat->is_lulus
                  ];
                  $urut++;
              }
              if(!sizeOf($data)) $datas = [];

              return response()->json([
                'success' => true,
                'message' => 'Import Data Berhasil',
                'req' => $req->all(),
                'data'  => $datas,
                'otoritas'  => HA::GetOtoritas(Auth::id(),3),
                'title' => $title,
                'id_sek'  => $sek->id,
              ]);
          }
    }


}
