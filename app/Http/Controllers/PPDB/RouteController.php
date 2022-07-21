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
use App\Http\Controllers\PPDB\PenerimaanController as Penerimaan;
use App\Http\Controllers\Sekolah\SekolahController as Sekolah;
use App\Http\Controllers\Siswa\SiswaController as Siswa;

class RouteController extends Controller
{
    //
    public function index(){

        return response()->json([
            'success'  => false,
            'message' => '.'
        ]);
    }

    public function IndexRouteSatu($satu, Request $req){

        if($satu == 'guest-cari-nisn-siswa-akhir') return Siswa::GuestGetSiswaAkhir($req);

        return response()->json([
            'success'  => false,
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

        elseif($satu == 'get-data-sekolah-register') return PPDB::DataSekReg($req);
        elseif($satu == 'guest' && $dua == 'get-data-sekolah') return Sekolah::GuestGetSekolahPPDB($req);
        elseif($satu == 'chek-jadwal-pendaftaran') return Jadwal::chekJadwalDaftar($req);
        elseif($satu == 'guest' && $dua == 'get-data-kuota-sekolah') return Penerimaan::GuestGetKuotaPPDB($req);

        elseif($satu == 'register-ppdb') return Register::RegisterPPDB($req);

        elseif($satu == 'operator-tambah-ppdb') return Register::OprAddPPDB($req);

        return response()->json([
            'success'  => false,
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
        }elseif($satu == 'home' && $dua == 'register-sekolah'){
            return PPDB::RegisterSekolah($tiga,$req);
        }
        elseif($satu == 'sekolah' && $dua == 'get-data-ppdb-info') return Penerimaan::GetDataPPDBInfo();
        elseif($satu == 'sekolah' && $dua == 'get-data-ppdb-kuota') return Penerimaan::GetDataPPDBKuota($tiga,$req);
        elseif($satu == 'sekolah' && $dua == 'get-data-registered') return Register::GetDataRegistered($tiga,$req);

        elseif($satu == 'sekolah' && $dua == 'filter-by-tahun-ajaran'){
            return PPDB::FilSekByTA($req);
        }elseif($satu == 'kecamatan' && $dua == 'filter-by-tahun-ajaran') return PPDB::FilKecByTA($req);

        elseif($satu == 'laporan' && $dua == 'registered') return Register::Registered($req);

        elseif($satu == 'jadwal' && $dua == 'get-by-sekolah') return Jadwal::GetBySek($tiga,$req);

        elseif($satu == 'peserta-didik' && $dua == 'cari-nik') return Siswa::GetSiswaByNIK($tiga,$req);

        return response()->json([
            'success'  => false,
            'message' => '...',
            'tiga'  => $satu.' # '.$dua.' # '.$tiga
        ]);
    }

}
