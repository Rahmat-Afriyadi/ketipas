<?php

namespace App\Http\Controllers\Sekolah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Sekolah\DataUmumController as DU;
use App\Http\Controllers\Sekolah\TendikController as Tendik;
use App\Http\Controllers\Sekolah\NonTendikController as NonTendik;
use App\Http\Controllers\Sekolah\PesertaDidikController as PesertaDidik;
use App\Http\Controllers\Sekolah\KelulusanController as Kelulusan;

class RouteController extends Controller
{
    //
    public function index(){

        return response()->json([
            'status'  => false,
            'message' => ''
        ]);
    }

    public function IndexRouteSatu($satu, Request $req){

        return response()->json([
            'status'  => false,
            'message' => '.'
        ]);
    }

    public function IndexRouteDua($satu, $dua, Request $req){
        if($satu == 'get-data-umum'){
            return DU::DataUmumSekolah($req);
        }elseif($satu == 'import-tenaga-pendidik'){
            return Tendik::ImportDataTendik($req);
        }

        elseif($satu == 'import-tenaga-kependidikan'){
            return NonTendik::ImportData($req);
        }

        elseif($satu == 'import-peserta-didik'){
            return PesertaDidik::ImportData($req);
        }elseif($satu == 'data-kelulusan-siswa'){
            return PesertaDidik::GetKelulusan($req);
        }

        return response()->json([
            'status'  => false,
            'message' => '..'
        ]);
    }

    public function IndexRouteTiga($satu, $dua, $tiga, Request $req){
        if($satu == 'update' && $dua == 'data-umum'){
            return DU::DataUmumSekolah($req);
        }

        elseif($satu == 'tenaga-pendidik' && $dua == 'get-by-ta'){
            return Tendik::GetByTA($req);
        }

        elseif($satu == 'tenaga-kependidikan' && $dua == 'get-by-ta'){
            return NonTendik::GetByTA($req);
        }

        elseif($satu == 'peserta-didik' && $dua == 'get-by-ta'){
            return PesertaDidik::GetByTA($req);
        }elseif($satu == 'detail' && $dua == 'data-kelulusan-siswa'){
            return Kelulusan::GetData($req);
        }elseif($satu == 'kelulusan' && $dua == 'update-nilai-kelulusan'){
            return Kelulusan::UpdateNilai($req);
        }elseif($satu == 'kelulusan' && $dua == 'update-status-kelulusan'){
            return Kelulusan::UpdateKelulusan($req);
        }

        return response()->json([
            'status'  => false,
            'message' => '...'
        ]);
    }

}
