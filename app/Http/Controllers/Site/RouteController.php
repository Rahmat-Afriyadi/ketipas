<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Site\RegulasiController as Regulasi;

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
        if($satu == 'get-regulasis'){
            return Regulasi::last_post();
        }elseif($satu == 'tambah-regulasi'){
            return Regulasi::store($req);
        }
        return response()->json([
            'status'  => false,
            'message' => '...'
        ]);
    }

}
