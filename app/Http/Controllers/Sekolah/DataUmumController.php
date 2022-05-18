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

class DataUmumController extends Controller{

    static function DataUmumSekolah($req){
        $id  = Auth::id();
        $id_user  = Auth::id();
        $operator  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
        if($operator){
            $data = DB::table('ta_sekolah')->where('id',$operator->id_sek)->first();
            if($data){
                if(\Request::isMethod('POST')){
                    $kecamatan  = DB::table('ref_kecamatan')->where('id',$req->id_kec)->first();
                    $ta  = DB::table('ref_tahun')->where('tahun',$req->kd_ta)->value('tahun_ajaran');
                    DB::table('ta_sekolah')->where('id',$data->id)->update([
                      'email'   => $req->email,
                      'prov'    => $req->prov,
                      'kab'     => $req->kab,
                      'kec'     => $kecamatan->uraian,
                      'id_kec'  => $req->id_kec,
                      'alamat'  => $req->alamat,
                      'tentang' => $req->tentang,
                      'kd_ta'   => $req->kd_ta,
                      'nm_kepsek'   => $req->nm_kepsek,
                      'nip_kepsek'   => $req->nip_kepsek,
                      'thn_ajaran'  => $ta,
                    ]);

                    if(isset($req->file)){
                        $path  = date('Y').'/'.date('m').'/';
                        $nm_file = $path.time() . '.' . $req->file('file')->getClientOriginalExtension();
                        $mime     = File::mimeType($req->file);
                        DB::table('ta_sekolah')->where('id',$data->id)->update([
                            'logo'  => $nm_file,
                            'logo_mime' => $mime
                        ]);
                        $req->file->storeAs('public/'.$path, time().'.'.$req->file('file')->getClientOriginalExtension() );
                    }

                    // return ['pesaan'=>'updaate data sukses xxdaa', 'req'=>$req->all()];
                }
                $data = DB::table('ta_sekolah')->where('id',$operator->id_sek)->first();
                if($data->logo) $logo = Config::getBucketAWS().$data->logo;
                else $logo = Config::getRefBucketAWS().'images/no_avatar.jpg';
                if($data->kd_ta != date('Y')){
                    $kd_ta = date('Y');
                }else{
                    $kd_ta = $data->kd_ta;
                }
                $kd_ta = $data->kd_ta;
                $respon  = [
                  'nama'  => $data->nama,
                  'email' => $data->email,
                  'nm_kepsek' => $data->nm_kepsek,
                  'nip_kepsek' => $data->nip_kepsek,
                  'prov'  => $data->prov,
                  'kab'   => $data->kab,
                  'kec'   => $data->kec,
                  'id_kec'   => $data->id_kec,
                  'alamat' => $data->alamat,
                  'tentang' => $data->tentang,
                  'thn_ajaran' => $data->thn_ajaran,
                  'kd_ta' => $kd_ta,
                  'logo'  => $logo,
                  'req' => $req->all()
                ];
            }else{
                $respon  = [];
            }
            $otoritas = true; $success = true; $message = 'Data Ditemukan';
        }else{
            $otoritas = false; $respon  = []; $message = 'Otoritas TIdak Tersedia';
        }


        return response()->json([
            'success'  => $success,
            'message' => $message,
            'otoritas'  => $otoritas,
            'data'  => $respon,
        ]);

    }


}
