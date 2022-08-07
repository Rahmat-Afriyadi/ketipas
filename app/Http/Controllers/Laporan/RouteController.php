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
use Illuminate\Support\Facades\DB;

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

    public function test_view(Request $request){
        $sek    = DB::table('ta_sekolah')->select('id','nama','email','alamat','jenjang','id_kec');
        $kec = DB::table('ref_kecamatan')->select('id','uraian')->where('id',$_GET['id_kec'])->first();
        if($_GET['jenjang']) $sek->where('jenjang',$_GET['jenjang']);  //tak usah dihapus
        if($_GET['id_kec']) $sek->where('id_kec',$_GET['id_kec']);
        $data  = $sek->get();
        return view('test',['data'=>$data->toArray(),'kec'=>$kec->uraian,'jenjang'=>$_GET['jenjang']]);
    }

    public function downloadPDF()
    {
        $sek  = DB::table('ta_sekolah')->select('id','nama','email','alamat','jenjang','id_kec');
        $kec = DB::table('ref_kecamatan')->select('id','uraian')->where('id',$_GET['id_kec'])->first();
        if($_GET['jenjang']) $sek->where('jenjang',$_GET['jenjang']);  //tak usah dihapus
        if($_GET['id_kec']) $sek->where('id_kec',$_GET['id_kec']);
        $data  = $sek->get();
        $pdf = PDF::setOptions(['defaultFont' => 'serif','isHtml5ParserEnabled' => true,'isRemoteEnabled' => true])->loadView('test', ['data'=>$data->toArray(),'kec'=>$kec->uraian,'jenjang'=>$_GET['jenjang']]);
        return $pdf->stream('test.pdf');
    }

}
