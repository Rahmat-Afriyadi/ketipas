<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Regulasi;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use File;
use Hash;

use App\Http\Controllers\Admin\HAController as HA;



class SekolahController extends Controller
{
    static function GetData($req){
        $success = false; $message = 'Gagal Get Data'; $otoritas = false;
        $user = Auth::user(); $datas = [];
        $ha  = HA::GetOtoritas($user->id,1);
        if($ha['admin']){
            $query = DB::table('ta_sekolah');
            if($req->id_kec) $query->where('id_kec',$req->id_kec);
            $data  = $query->get();
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
            if(!sizeOf($data)) $datas  = [];
            $otoritas = true; $success = true; $message = 'Sukses Get Data';
        }

        return response()->json([
            'success'  => $success,
            'message' => $message,
            'data'  => $datas,
            'otoritas'  => $otoritas,
        ]);

    }

    static function FilterByIdKecamatan($req){
        $success = false; $message = 'Gagal Get Data'; $otoritas = false;
        $user = Auth::user(); $datas = [];
        $ha  = HA::GetOtoritas($user->id,1);
        if($ha['admin']){
            $data = DB::table('ta_sekolah')->where('id_kec',$req->id_kec)->get();
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
            if(!sizeOf($data)) $datas  = [];
            $otoritas = true; $success = true; $message = 'Sukses Get Data';
        }

        return response()->json([
            'success'  => $success,
            'message' => $message,
            'data'  => $datas,
        ]);
    }

    static function GetById($url,$req){
        $exp  = explode('-',$url); $success = false; $message = 'Gagal';
        $data = DB::table('ta_sekolah')->where('id',$exp[0])->first();
        if($data){
            $success = true; $message = 'Sukses';
        }

        return response()->json([
            'success'  => $success,
            'message' => $message,
            'data'  => $data,
        ]);
    }

    static function UpdateData($url,$req){
        $exp  = explode('-',$url); $success = false; $message = 'Gagal';
        $data = DB::table('ta_sekolah')->where('id',$exp[0])->first();
        if($data){
            DB::table('ta_sekolah')->where('id',$exp[0])->update([
                'alamat'  => $req->alamat,
                'id_kec'  => $req->id_kec,
                'status'  => $req->status
            ]);
            $success = true; $message = 'Update Data ';
        }

        return response()->json([
            'success'  => $success,
            'message' => $message,
            'data'  => $data,
            'req' => $req->all(),
        ]);
    }

    static function GetOperatorSekolah($url,$req){
        $exp  = explode('-',$url); $success = false; $message = 'Gagal'; $operator = [];
        $sekolah = DB::table('ta_sekolah')->where('id',$exp[0])->first();
        if($sekolah){
            $operator  = DB::table('ta_sekolah_opr')->where('id_sek',$exp[0])->get();
            $success   = true; $message = 'Update Data ';
        }

        return response()->json([
            'success'  => $success,
            'message' => $message,
            'sekolah'  => $sekolah,
            'operator'  => $operator,
        ]);
    }

    static function TambahOperator($req){
        $success = false; $message = 'Gagal';
        $user  = User::where('id',$req->id_user)->first();
        if($user){
            $data  = DB::table('ta_sekolah_opr')->where('id_user',$req->id_user)->first();
            if($data){
                DB::table('ta_sekolah_opr')->where('id_user',$req->id_user)->update(['id_sek'=>$req->id_sek, 'status'=>1]);
            }else{
                DB::table('ta_sekolah_opr')->insert([
                    'id_sek'  => $req->id_sek,
                    'id_user'  => $req->id_user,
                    'nama'  => $user->name,
                    'email' => $user->email,
                    'status'  => 1,
                ]);
            }
            $success = true; $message = 'Berhasil Tambah Data Operator';
        }

        $data  = DB::table('ta_sekolah_opr')->where('id_sek',$req->id_sek)->get();

        return response()->json([
            'success'  => $success,
            'message' => $message,
            'data'  => $data,
        ]);
    }

    static function UpdateStatusOperator($req){
        $cek   = DB::table('ta_sekolah_opr')->where('id',$req->id)->first();
        if($cek){
            if($req->status) $status = 0;
            else $status = 1;
            DB::table('ta_sekolah_opr')->where('id',$req->id)->update([
                'status'  => $status
            ]);
            $message = 'Sukses Update Status Operator';
            $success = true;
        }else{
            $success = false;
            $message = 'Data Tidak Ditemukan';
        }
        return response()->json([
            'success'  => $success,
            'message' => $message,
        ]);
    }


}
