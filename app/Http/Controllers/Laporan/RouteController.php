<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Http\Controllers\Laporan\PesertaDidikController as PD;

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


        return response()->json([
            'status'  => false,
            'message' => '...',
            'data'  => $satu.' # '.$dua
        ]);
    }

    public function IndexRouteTiga($satu, $dua, $tiga, Request $req){
        if($satu == 'vaksin' && $dua == 'filter-pd'){
            return PD::FilterPesertaDidik($tiga,$req);
        }
        elseif($satu == 'peserta-didik' && $dua == 'filter-data-dua') return PD::FilterDataDua($req);

        return response()->json([
            'status'  => false,
            'message' => '...',
            'tiga'  => $satu.' # '.$dua.' # '.$tiga
        ]);
    }

}
