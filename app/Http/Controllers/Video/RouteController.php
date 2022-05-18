<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Video\VideoController as Video;

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
            return Video::last_post();
        }
        return response()->json([
            'status'  => false,
            'message' => '..'
        ]);
    }

    public function IndexRouteDua($satu, $dua, Request $req){
        if($satu == 'show'){
            return Video::show($dua);
        }elseif($satu == 'tambah-video'){
            return Video::store($req);
        }elseif($satu == 'get-data'){
            return Video::GetData($req);
        }elseif($satu == 'get-by-id'){
            return Video::GetById($dua);
        }elseif($satu == 'update-video'){
            return Video::UpdateData($dua,$req);
        }
        return response()->json([
            'status'  => false,
            'message' => '...'
        ]);
    }

}
