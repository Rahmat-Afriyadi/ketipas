<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\News\NewsController as News;

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
            // api web
            return News::last_post();
        }
        return response()->json([
            'status'  => false,
            'message' => '..'
        ]);
    }

    public function IndexRouteDua($satu, $dua, Request $req){
        if($satu == 'show'){
            return News::show($dua);
        }elseif($satu == 'tambah-berita'){
            return News::store($req);
        }elseif($satu == 'update-berita'){
            return News::UpdateBerita($dua,$req);
        }elseif($satu == 'get-by-id'){
            return News::GetById($dua);
        }elseif($satu == 'last-post'){
            return News::last_post();
        }
        return response()->json([
            'status'  => false,
            'message' => '...'
        ]);
    }

}
