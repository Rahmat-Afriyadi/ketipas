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
use App\Http\Controllers\Sekolah\SarprasController as Sarpras;
use App\Http\Controllers\Sekolah\KPController as KP;
use App\Http\Controllers\Sekolah\KGBController as KGB;
use App\Http\Controllers\Sekolah\SekolahController as Sekolah;
use App\Http\Controllers\Vaksin\VaksinController as Vaksin;

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

        elseif($satu == 'sarana'){
            return Sarpras::GetSaranaByTA($req);
        }elseif($satu == 'import-sarana'){
            return Sarpras::ImportSarana($req);
        }

        elseif($satu == 'prasarana'){
            return Sarpras::GetPrasaranaByTA($req);
        }elseif($satu == 'import-prasarana'){
            return Sarpras::ImportPrasarana($req);
        }

        elseif($satu == 'kenaikan-pangkat'){
            return KP::GetKP($req);
        }

        elseif($satu == 'kgb'){
            return KGB::GetKGB($req);
        }

        elseif($satu == 'filter-sekolah-by-kecamatan'){
            return Sekolah::GetSekByKec($req);
        }
        elseif($satu == 'filter-sekolah-by-jenis'){
            return Sekolah::GetSekByJenis($req);
        }
        elseif($satu == 'filter-sekolah-satu'){
            return Sekolah::FilterSekolahSatu($req);
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
        }elseif($satu == 'data' && $dua == 'tenaga-pendidik'){
            return Tendik::TenagaPendidik($req);
        }

        elseif($satu == 'tenaga-kependidikan' && $dua == 'get-by-ta'){
            return NonTendik::GetByTA($req);
        }elseif($satu == 'data' && $dua == 'tenaga-kependidikan'){
            return NonTendik::GetNonTendik($req);
        }

        elseif($satu == 'peserta-didik' && $dua == 'get-by-ta'){
            return PesertaDidik::GetByTA($req);
        }elseif($satu == 'detail' && $dua == 'data-kelulusan-siswa'){
            return Kelulusan::GetData($req);
        }elseif($satu == 'kelulusan' && $dua == 'update-nilai-kelulusan'){
            return Kelulusan::UpdateNilai($req);
        }elseif($satu == 'kelulusan' && $dua == 'update-status-kelulusan'){
            return Kelulusan::UpdateKelulusan($req);
        }elseif($satu == 'data' && $dua == 'peserta-didik'){
            return PesertaDidik::GetPesertaDidik($req);
        }

        elseif($satu == 'kenaikan-pangkat' && $dua == 'detail'){
            return KP::DetailKP($tiga);
        }elseif($satu == 'kenaikan-pangkat' && $dua == 'update'){
            return KP::UpdateKP($tiga,$req);
        }

        elseif($satu == 'kgb' && $dua == 'detail'){
            return KGB::DetailKGB($tiga);
        }elseif($satu == 'kgb' && $dua == 'update'){
            return KGB::UpdateKGB($tiga,$req);
        }

        elseif($satu == 'vaksin' && $dua == 'filter-tenaga-pendidik'){
            return Vaksin::FilterTenagaPendidik($req);
        }elseif($satu == 'vaksin' && $dua == 'filter-tenaga-kependidikan'){
            return Vaksin::FilterTenagaKependidikan($req);
        }



        return response()->json([
            'status'  => false,
            'message' => '...'
        ]);
    }

}
