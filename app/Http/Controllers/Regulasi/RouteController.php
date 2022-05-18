<?php

namespace App\Http\Controllers\Regulasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Regulasi\RegulasiController as Regulasi;

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
        // if($satu == 'last_post'){
        //     return Regulasi::last_post();
        // }
        return response()->json([
            'status'  => false,
            'message' => '..'
        ]);
    }

    public function IndexRouteDua($satu, $dua, Request $req){
        if($satu == 'show'){
            return Regulasi::show($dua);
        }elseif($satu == 'get-by-id'){
            return Regulasi::GetById($dua);
        }elseif($satu == 'update-regulasi'){
            return Regulasi::UpdateRegulasi($dua,$req);
        }

        return response()->json([
            'status'  => false,
            'message' => '...'
        ]);
    }

}
