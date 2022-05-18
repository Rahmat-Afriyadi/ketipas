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



class KecamatanController extends Controller
{
    static function GetData($req){
        $success = false; $message = 'Gagal Get Data'; $otoritas = false;
        $user = Auth::user(); $datas = [];
        $ha  = HA::GetOtoritas($user->id,1);
        if($ha['admin']){
            $data = DB::table('ref_kecamatan')->get();
            foreach($data as $dat){
                if($dat->status) $nm_status = 'Aktif';
                else $nm_status = 'Tidak Aktif';
                $datas[] = [
                  'id'  => $dat->id,
                  'kode_wilayah'  => $dat->kode_wilayah,
                  'uraian' => $dat->uraian,
                  'status'  => $dat->status,
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


}
