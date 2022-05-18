<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Regulasi;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use File;
use Hash;

use App\Http\Controllers\Admin\HAController as HA;



class UsersController extends Controller
{
    static function GetData($req){
        $success = false; $message = 'Gagal Get Data'; $otoritas = false;
        $user = Auth::user(); $datas = [];
        $ha  = HA::GetOtoritas($user->id,1);
        if($ha['admin']){
            $per_page = isset($_GET['per_page']) ? $_GET['per_page'] : 4 ;
            if($req->name){
                $datas = User::where('name','LIKE','%'.$req->name.'%')->orderBy('created_at', 'desc')->get();
            }else{
                $datas = User::orderBy('created_at', 'desc')->get();
            }


            $otoritas = true;
        }
        return response()->json([
            'success'  => $success,
            'message' => $message,
            'data'  => $datas,
            'ha'  => $ha,
            'otoritas'  => $otoritas,
        ]);
    }

    static function UpdateStatusUser($req){
        $cek  = User::where('id',$req->id)->where('id','!=',1)->first();
        if($cek){
            if($req->status) $status = 0;
            else $status = 1;
            User::where('id',$req->id)->update([
                'status'  => $status,
            ]);
            $success  = true; $message = 'Update Status Berhasil';
        }else{
            $success  = false; $message = 'Data User Tidak Ditemukan';
        }
        return response()->json([
            'success'  => $success,
            'message' => $message,
        ]);
    }

    static function UpdatePassword($req){
        $cek  = User::where('id',$req->id)->first();
        if($cek){
            $password  = 'ketipas'.rand(1111,9999);
            $update    = User::where('id',$req->id)->update([
                            'password' => Hash::make($password),
                         ]);
            if($update){
                $success = true; $message = 'Reset Password Berhasil';
            }else{
                $success = false; $message = 'Data User Tidak Ditemukan';
            }
        }else{
            $success = false; $message = 'User Tidak Ditemukan'; $password = '';
        }

        return response()->json([
            'success'  => $success,
            'message' => $message,
            'password'  => $password,
        ]);
    }

    static function CariAutoUsers($req){
        $key  = $req->cari;
        $data      = User::where('name','LIKE','%'.$key.'%')->orWhere('email','LIKE','%'.$key.'%')->get();
        if(sizeOf($data)){
            foreach($data as $dat){
              $datas[] = [
                'id'    => $dat->id,
                'groupId'   => $dat->id,
                'name'  => $dat->name.' ('.$dat->email.')',
              ];
            }
        }else $datas = [];
        return response()->json([
          'data' => $datas,
          'success' => true,
          'message' => 'Sukses',
          'req' => $req->all()
        ]);
    }


}
