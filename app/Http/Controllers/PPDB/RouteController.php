<?php

namespace App\Http\Controllers\PPDB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\PPDB\PPDBController as PPDB;
use App\Http\Controllers\PPDB\LokasiController as Lokasi;
use App\Http\Controllers\PPDB\RegisterController as Register;

class RouteController extends Controller
{
    //
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
        if($satu == 'get-data-jadwal'){
            return PPDB::GetJadwal($req);
        }
        elseif($satu == 'get-data-lokasi-ppdb'){
            return Lokasi::GetLokasi($req);
        }
        elseif($satu == 'cek-status-pendaftaran'){
            return Register::CekStatusPendaftaran($req);
        }
        return response()->json([
            'status'  => false,
            'message' => '...',
            'data'  => $satu.' # '.$dua
        ]);
    }

}
