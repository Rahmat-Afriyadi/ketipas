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
use Session;
use App\Imports\SpImport;
use App\Imports\PrImport;
use Maatwebsite\Excel\Facades\Excel;

class SarprasController extends Controller{
    private static $thn = 2021;
    static function GetSaranaByTA($req){
        $id_user  = Auth::ID(); $success = false; $sekolah = ''; $import = false;
        $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
        if($opr){
            $success = true;
            Session::put('id_sek', $opr->id_sek);
            Session::put('ta', $req->tahun);
            $data  = DB::table('ta_sarana')->where('id_sek',$opr->id_sek)->where('ta',$req->tahun)->get();
            $sekolah = DB::table('ta_sekolah')->select('id','id_kec','nama','email')->where('id',$opr->id_sek)->first();
            if($req->tahun == static::$thn){
                $import = true;
            }

        }else{
            Session::put('id_sek', 0);
            Session::put('ta', 0);
            $data  = [];
            $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
        }

        return response()->json([
          'success' => $success,
          'message' => 'sarparas',
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

    static function ImportSarana($req){
        $id_user  = Auth::ID();
        $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();

        Session::put('id_sek', $opr->id_sek);
        Session::put('ta', static::$thn);
        $id_sek  = Session::get('id_sek');
        $ta  = Session::get('ta');

        DB::table('ta_sarana')->where('id_sek',$id_sek)->where('ta',$ta)->delete();
        Excel::import(new SpImport,request()->file('file'));
        $data  = DB::table('ta_sarana')->where('ta',$ta)->where('id_sek',$id_sek)->get();

        return [
          'success' => true,
          'message' => 'Import Data Berhasil',
          'data'  => $data,
          'req' => $req->all(),
        ];
    }

    static function GetPrasaranaByTA($req){
        $id_user  = Auth::ID(); $success = false; $sekolah = ''; $import = false; $message = 'Gagal Get Data';
        $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
        if($opr){
            $success = true; $message = 'Sukses Get Data';
            Session::put('id_sek', $opr->id_sek);
            Session::put('ta', $req->tahun);
            $data  = DB::table('ta_prasarana')->where('id_sek',$opr->id_sek)->where('ta',$req->tahun)->get();
            $sekolah = DB::table('ta_sekolah')->select('id','id_kec','nama','email')->where('id',$opr->id_sek)->first();
            if($req->tahun == static::$thn){
                $import = true;
            }

        }else{
            Session::put('id_sek', 0);
            Session::put('ta', 0);
            $data  = [];
            $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
        }

        return response()->json([
          'success' => $success,
          'message' => $message,
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

    static function ImportPrasarana($req){
        $id_user  = Auth::ID();
        $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();

        Session::put('id_sek', $opr->id_sek);
        Session::put('ta', static::$thn);
        $id_sek  = Session::get('id_sek');
        $ta  = Session::get('ta');

        DB::table('ta_prasarana')->where('id_sek',$id_sek)->where('ta',$ta)->delete();
        Excel::import(new PrImport,request()->file('file'));
        $data  = DB::table('ta_prasarana')->where('ta',$ta)->where('id_sek',$id_sek)->get();

        return response()->json([
          'success' => true,
          'message' => 'Import Data Berhasil',
          'data'  => $data,
          'req' => $req->all(),
        ]);
    }


}
