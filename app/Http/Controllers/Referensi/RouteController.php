<?php

namespace App\Http\Controllers\Referensi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Referensi\ReferensiController as Referensi;

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
        if($satu == 'last_post'){
            return News::last_post();
        }
        return response()->json([
            'status'  => false,
            'message' => '..'
        ]);
    }

    public function IndexRouteDua($satu, $dua, Request $req){
        if($satu == 'get-tahun'){
            return Referensi::GetTahun();
        }elseif($satu == 'get-kecamatan'){
            return Referensi::GetKecamatan();
        }
        return response()->json([
            'status'  => false,
            'message' => '...'
        ]);
    }

}
