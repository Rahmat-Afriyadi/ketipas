<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use View;
use Str;


class HAController extends Controller{

    static function GetOtoritas($id_user,$id_ha){
        // 1. User Admin
        // 2. Kepala Dinas
        // 3. Operator
        $lihat = 0;  $tambah = 0;  $edit = 0; $khusus = 0; $operator = 0; $id_sek = 0;
        $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
        if($opr){
            $lihat = 0;  $tambah = 0;  $edit = 0; $id_sek = $opr->id_sek; $operator = $opr->id_sek;
        }

        $hk  = DB::table('ta_hak_akses')->where('id_user',$id_user)->where('id_hak_akses',$id_ha)->where('status',1)->first();
        if($hk){
            $lihat = $hk->lihat;  $tambah = $hk->tambah;  $edit = $hk->edit; $khusus = $hk->lihat;
            $operator = 1;
        }

        $admin  = DB::table('users')->where('id',$id_user)->where('admin',1)->where('status',1)->count();
        if($admin){
            $lihat = 1;  $tambah = 1;  $edit = 1; $admin = 1;  $khusus = 1; $operator = 1;
        }

        return [
            'lihat' => $lihat,
            'tambah'  => $tambah,
            'edit'  => $edit,
            'admin' => $admin,
            'khusus'  => $khusus,
            'operator'  => $operator,
            'id_sek'  => $id_sek,
            'datax' => $id_user.' - '.$id_ha,
        ];
    }

    static function GetHakAkses($req){
        $id_user  = $req->id_user;

        $master = DB::table('ref_hak_akses')->get();
        foreach($master as $dat){
            $cek  = DB::table('ta_hak_akses')->where('id_user',$id_user)->where('id_hak_akses',$dat->id)->first();
            if($cek){
                DB::table('ta_hak_akses')->where('id',$cek->id)->update([
                    'urut'  => $dat->urut,
                    'uraian'  => $dat->uraian,
                    'keterangan'  => $dat->keterangan,
                ]);
            }else{
                DB::table('ta_hak_akses')->insert([
                    'id_user' => $id_user,
                    'id_hak_akses'  => $dat->id,
                    'urut'  => $dat->urut,
                    'uraian'  => $dat->uraian,
                    'keterangan'  => $dat->keterangan,
                ]);
            }
        }

        $data  = DB::table('ta_hak_akses')->where('id_user',$id_user)->get();
        $jabatan  = DB::table('users')->select('admin','kadis','pengawas')->where('id',$id_user)->first();

        return response()->json([
            'success'  => true,
            'message' => 'Sukses Update Data',
            'data'  => $data,
            'otoritas'  => true,
            'jabatan' => $jabatan,
        ]);
    }

    static function UpdateHakAkses($url,$req){
        $exp  = explode("-", $url);
        if($req->status) $status = 0;
        else $status = 1;
        DB::table('ta_hak_akses')->where('id',$req->id)->update([
            $exp[0] => $status,
        ]);

        return response()->json([
            'success'  => true,
            'message' => 'Sukses Update Data',
        ]);
    }

    static function UpdateJabatan($req){
        $success  = 1; $message = 'Gagal Update Jabatan, User Tidak Ditemukan';
        $admin  = DB::table('users')->where('id',Auth::id())->where('admin',1)->first();
        if($admin){
            $cek  = DB::table('users')->where('id',$req->id_user)->first();
            if($cek){
                if($req->jabatan == 'kadis'){
                  $admin = 0; $kadis = 1; $pengawas = 0;
                  $ha  = DB::table('ta_hak_akses')->where('id_user',$req->id_user)->get();
                  foreach($ha as $dat){
                      DB::table('ta_hak_akses')->where('id',$dat->id)->update([
                          'tambah'  => 1,
                          'lihat' => 1,
                          'edit'  => 1
                      ]);
                  }
                }elseif($req->jabatan == 'pengawas'){
                  $admin = 0; $kadis = 0; $pengawas = 1;
                  $ha  = DB::table('ta_hak_akses')->where('id_user',$req->id_user)->get();
                  foreach($ha as $dat){
                      DB::table('ta_hak_akses')->where('id',$dat->id)->update([
                          'tambah'  => 1,
                          'lihat' => 1,
                          'edit'  => 1
                      ]);
                  }
                }elseif($req->jabatan == 'admin'){
                  $admin = 1; $kadis = 0; $pengawas = 0;
                  $ha  = DB::table('ta_hak_akses')->where('id_user',$req->id_user)->get();
                  foreach($ha as $dat){
                      DB::table('ta_hak_akses')->where('id',$dat->id)->update([
                          'tambah'  => 1,
                          'lihat' => 1,
                          'edit'  => 1
                      ]);
                  }
                }else {
                  $admin = 0; $kadis = 0; $pengawas = 0;
                  $ha  = DB::table('ta_hak_akses')->where('id_user',$req->id_user)->get();
                  foreach($ha as $dat){
                      DB::table('ta_hak_akses')->where('id',$dat->id)->update([
                          'tambah'  => 0,
                          'lihat' => 0,
                          'edit'  => 0
                      ]);
                  }
                }
                DB::table('users')->where('id',$req->id_user)->update([
                    'admin' => $admin,
                    'kadis' => $kadis,
                    'pengawas'  => $pengawas,
                ]);
            }
        }else{
            $success  = 1; $message = 'Update Jabatan Harus Menggunakan User Admin';
        }

        $jabatan  = DB::table('users')->select('admin','kadis','pengawas')->where('id',$req->id_user)->first();

        return response()->json([
            'success'  => $success,
            'message' => $message,
            'jabatan' => $jabatan,
            'ha'  => $data  = DB::table('ta_hak_akses')->where('id_user',$req->id_user)->get()
        ]);
    }

}
