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
use App\Http\Controllers\PPDB\JadwalController as Jadwal;

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

        elseif($satu == 'get-data-pelaksana-ppdb'){
            return PPDB::GedDataPelaksana();
        }elseif($satu == 'tambah-data-informasi'){
            return PPDB::TambahDataInformasi($req);
        }elseif($satu == 'hapus-data-informasi'){
            return PPDB::HapusDataInformasi($req);
        }

        return response()->json([
            'status'  => false,
            'message' => '...',
            'data'  => $satu.' # '.$dua
        ]);
    }

    public function IndexRouteTiga($satu, $dua, $tiga, Request $req){
        if($satu == 'home' && $dua == 'get-data-informasi'){
            return PPDB::GetDataInformasi();
        }elseif($satu == 'home' && $dua == 'edit-data-informasi'){
            return PPDB::EditDataInformasi($tiga,$req);
        }elseif($satu == 'home' && $dua == 'get-informasi-aturan'){
            return PPDB::GetInformasiAturan($tiga,$req);
        }elseif($satu == 'home' && $dua == 'get-info-jadwal'){
            return Jadwal::GetApiJadwal($tiga,$req);
        }

        return response()->json([
            'status'  => false,
            'message' => '...',
            'tiga'  => $satu.' # '.$dua.' # '.$tiga
        ]);
    }

}
