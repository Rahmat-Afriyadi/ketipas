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
use App\Imports\PDImport;
use Maatwebsite\Excel\Facades\Excel;

class KGBController extends Controller{

    static function GetKGB($req){
        $id_user  = Auth::ID(); $success = false; $sekolah = ''; $import = false; $data = [];
        $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
        if($opr){
            $ta  = DB::table('ref_tahun')->where('aktif',1)->first();
            if($ta){
                $success = true;
                if($req->jenis == 1){
                    $data  = DB::table('ta_tendik')->where('id_sek',$opr->id_sek)->where('ta',$ta->tahun)->get();
                    foreach($data as $dat){
                        $tanggal  = DB::table('ta_kgb')->where('nik',$dat->nik)->value('tanggal');
                        if(!$tanggal){
                            $tanggal = '-';
                        }
                        $datas[]  = [
                          'id'  => $dat->id,
                          'nuptk' => $dat->nuptk,
                          'nama'  => $dat->nama,
                          'agama' => $dat->agama,
                          'email' => $dat->email,
                          'nik' => $dat->nik,
                          'tanggal' => $tanggal,
                        ];
                    }
                }elseif($req->jenis == 2){
                    $data  = DB::table('ta_non_tendik')->where('id_sek',$opr->id_sek)->where('ta',$ta->tahun)->get();
                    foreach($data as $dat){
                        $tanggal  = DB::table('ta_kgb')->where('nik',$dat->nik)->value('tanggal');
                        if(!$tanggal){
                            $tanggal = '-';
                        }
                        $datas[]  = [
                          'id'  => $dat->id,
                          'nuptk' => $dat->nuptk,
                          'nama'  => $dat->nama,
                          'agama' => $dat->agama,
                          'email' => $dat->email,
                          'nik' => $dat->nik,
                          'tanggal' => $tanggal,
                        ];
                    }
                }
                $sekolah = DB::table('ta_sekolah')->select('id','id_kec','nama','email')->where('id',$opr->id_sek)->first();
            }

        }else{
            Session::put('id_sek', 0);
            Session::put('ta', 0);
            $data  = [];
            $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
        }

        if(!sizeOf($data)) $datas = [];

        return response()->json([
            'success' => $success,
            'req'  => $req->all(),
            'data'  => $datas,
            'opr' => $opr,
            'otoritas'  => $success,
            'import'  => $import,
            'sekolah'  => $sekolah,
        ]);
    }

    static function DetailKGB($url){
        $id_user  = Auth::ID(); $success = false; $sekolah = ''; $import = false;
        $data = []; $message = 'Gagal Get Data'; $tanggal = ''; $dat = '';
        $exp  = explode('-',$url);

        if($exp[1] == 1){
            $dat  = DB::table('ta_tendik')->where('id',$exp[0])->first();
            if($dat){
                $tanggal = DB::table('ta_kgb')->where('nik',$dat->nik)->value('tanggal');
                if(!$tanggal){
                    $tanggal = '-';
                }
                $success = true;
            }
        }elseif($exp[1] == 2){
            $dat  = DB::table('ta_non_tendik')->where('id',$exp[0])->first();
            if($dat){
                $tanggal = DB::table('ta_kgb')->where('nik',$dat->nik)->value('tanggal');
                if(!$tanggal){
                    $tanggal = '-';
                }
                $success = true;
            }
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $dat,
            'tanggal' => $tanggal,
            'url' => $url
        ]);
    }

    static function UpdateKGB($url,$req){
        $exp  = explode('-',$url);
        $jenis = $exp[1];

        $bulan  = ['Jan'=>'01','Feb'=>'02','Mar'=>'03', 'Apr'=>'04', 'May'=>'05', 'Jun'=>'06', 'Jul'=>'07', 'Aug'=>'08', 'Sep'=>'09', 'Oct'=>'10', 'Nov'=>'11', 'Dec'=>'12'];
        if(!isset($req->tanggal)){
            return response()->json([
                'success' => false,
                'message' => 'Silahkan Isi Tanggal',
            ]);
        }
        $strip = substr($req->tanggal,4,1);
        if($strip == '-'){
            $tanggal = substr($req->tanggal,0,10);
        }else{
            $arr = explode(' ', $req->tanggal);
            if(sizeOf($arr) > 1){
              $tanggal = $arr[3].'-'.$bulan[$arr[1]].'-'.$arr[2];
            }
        }

        $cek  = DB::table('ta_kgb')->where('nik',$req->nik)->first();
        if($cek){
            DB::table('ta_kgb')->where('nik',$req->nik)->update([
                'tanggal' => $tanggal,
                'jenis' => $jenis,
            ]);
        }else{
            DB::table('ta_kgb')->insert([
                'nama' => $req->nama,
                'nik' => $req->nik,
                'jenis' => $jenis,
                'tanggal' => $tanggal,
            ]);
        }
        return [
          'success' => true,
          'message' => 'Sukses Update Data',
          'tanggal' => $tanggal,
          'req' => $req->all(),
        ];
    }


}
