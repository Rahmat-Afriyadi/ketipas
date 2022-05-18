<?php

namespace App\Http\Controllers\Galeri;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Galeri\GaleriController as Galeri;

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
            return Galeri::last_post();
        }
        return response()->json([
            'status'  => false,
            'message' => '..'
        ]);
    }

    public function IndexRouteDua($satu, $dua, Request $req){
        if($satu == 'show'){
            return Galeri::show($dua);
        }elseif($satu == 'tambah-galeri'){
            return Galeri::store($req);
        }elseif($satu == 'last-post'){
            return Galeri::LastPost();
        }elseif($satu == 'get-by-slug'){
            return Galeri::GetBySlug($dua);
        }elseif($satu == 'update-galeri'){
            return Galeri::UpdateGaleri($dua,$req);
        }
        return response()->json([
            'status'  => false,
            'message' => '...'
        ]);
    }

}
