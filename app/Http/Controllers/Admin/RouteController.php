<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Admin\UsersController as Users;
use App\Http\Controllers\Admin\SekolahController as Sekolah;
use App\Http\Controllers\Admin\KecamatanController as Kecamatan;
use App\Http\Controllers\Admin\HAController as HA;

class RouteController extends Controller
{

    public function index(){

        return response()->json([
            'status'  => false,
            'message' => '.'
        ]);
    }

    public function IndexRouteSatu($satu, Request $req){

        return response()->json([
            'status'  => false,
            'message' => '..'
        ]);
    }

    public function IndexRouteDua($satu, $dua, Request $req){

        return response()->json([
            'status'  => false,
            'message' => '...'
        ]);
    }

    public function IndexRouteTiga($satu, $dua, $tiga, Request $req){
        if($satu == 'users' && $dua == 'get-data'){
            return Users::GetData($req);
        }elseif($satu == 'users' && $dua == 'get-hak-akses'){
            return HA::GetHakAkses($req);
        }elseif($satu == 'users' && $dua == 'update-jabatan'){
            return HA::UpdateJabatan($req);
        }elseif($satu == 'users' && $dua == 'update-status-user'){
            return Users::UpdateStatusUser($req);
        }elseif($satu == 'users' && $dua == 'update-hak-akses'){
            return HA::UpdateHakAkses($tiga,$req);
        }elseif($satu == 'users' && $dua == 'update-password'){
            return Users::UpdatePassword($req);
        }elseif($satu == 'users' && $dua == 'cari-data-users'){
            return Users::CariAutoUsers($req);
        }


        elseif($satu == 'sekolah' && $dua == 'get-data'){
            return Sekolah::GetData($req);
        }elseif($satu == 'sekolah' && $dua == 'filter-sekolah-by-kecamatan'){
            return Sekolah::FilterByIdKecamatan($req);
        }elseif($satu == 'sekolah' && $dua == 'get-by-id'){
            return Sekolah::GetById($tiga,$req);
        }elseif($satu == 'sekolah' && $dua == 'update-data'){
            return Sekolah::UpdateData($tiga,$req);
        }elseif($satu == 'sekolah' && $dua == 'get-operator'){
            return Sekolah::GetOperatorSekolah($tiga,$req);
        }elseif($satu == 'sekolah' && $dua == 'tambah-data-operator'){
            return Sekolah::TambahOperator($req);
        }elseif($satu == 'sekolah' && $dua == 'update-status-operator'){
            return Sekolah::UpdateStatusOperator($req);
        }

        elseif($satu == 'kecamatan' && $dua == 'get-data'){
            return Kecamatan::GetData($req);
        }

        return response()->json([
            'status'  => false,
            'message' => '....'
        ]);
    }

}
