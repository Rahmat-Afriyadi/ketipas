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
use App\Imports\TendikImport;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Config\ConfigController as Config;
use App\Http\Controllers\Admin\HAController as HA;

class TendikController extends Controller{
    private static $tahun = 2021;
    static function GetByTA($req){
        $id_user  = Auth::ID(); $success = false; $sekolah = ''; $import = false;
        $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
        if($opr){
            $success = true;
            Session::put('id_sek', $opr->id_sek);
            $ta  = DB::table('ref_tahun')->where('id',$req->ta)->first();
            Session::put('ta', $ta->tahun);
            $data  = DB::table('ta_tendik')->where('id_sek',$opr->id_sek)->where('ta',$ta->tahun)->get();
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

    static function TenagaPendidik($req){
        $success = false; $message = 'Gagal Get Data'; $data = [];
        $id_user  = Auth::id();
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

        $data  = DB::table('ta_tendik')->whereIn('id_sek',$id_sek)->where('ta',$req->thn_ajar)->get();

        $success = true;
        return response()->json([
          'success' => $success,
          'message' => $message,
          'data'  => $data,
        ]);
    }

    static function ImportDataTendik($req){
        $id_user  = Auth::ID();
        $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();

        Session::put('id_sek', $opr->id_sek);
        Session::put('ta', static::$tahun);
        $id_sek  = Session::get('id_sek');
        $ta  = Session::get('ta');
        DB::table('ta_tendik')->where('id_sek',$id_sek)->where('ta',$ta)->delete();
        Excel::import(new TendikImport,request()->file('file'));
        $data  = DB::table('ta_tendik')->where('ta',$ta)->where('id_sek',$id_sek)->get();
        $thn = static::$tahun;
        return response()->json([
          'success' => true,
          'message' => 'Import Data Berhasil',
          'data'  => $data,
          'req' => $req->all(),
          'session' => Session::get('id_sek').' dan '.Session::get('ta'),
          'tahun' => $thn,
        ]);
    }


}
