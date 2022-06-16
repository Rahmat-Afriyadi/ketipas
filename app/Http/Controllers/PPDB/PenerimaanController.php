<?php

namespace App\Http\Controllers\PPDB;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use View;
use Str;
use Crypt;

class PenerimaanController extends Controller{

    static function GetDataPPDBInfo(){
        $id_user  = Auth::id();
        $sekolah  = DB::table('ta_sekolah as sek')->join('ta_sekolah_opr as opr','opr.id_sek','sek.id')
                    ->select('sek.id_inst','sek.nama','sek.id as id_sek')->where('opr.id_user',$id_user)->where('opr.status',1)->first();
        if($sekolah){
            $urut  = 1;
            $data  = DB::table('ta_ppdb_sek')->where('id_sek',$sekolah->id_sek)->get();
            foreach($data as $dat){
                self::HitungPPDBReg($dat->id);
                if($dat->status) $status = 'Aktif';
                else $status = 'Tidak Aktif';
                if($dat->pelaksanaan) $ur_pel = 'Online';
                else $ur_pel = 'Offline';
                $datas[] = [
                  'urut'  => $urut,
                  'id'  => $dat->id,
                  'id_thn' => $dat->id_thn,
                  'tahun' => $dat->tahun,
                  'thn_ajar'  => $dat->tahun_ajaran,
                  'status'  => $dat->status,
                  'nm_status'  => $status,
                  'id_sek'  => $sekolah->id_sek,
                  'nm_sek'  => $dat->nm_sek,
                  'laki'  => $dat->laki,
                  'perempuan'  => $dat->perempuan,
                  'pelaksanaan'  => $dat->pelaksanaan,
                  'ur_pel'  => $ur_pel,
                  'total'  => $dat->total,
                  'id_sek_crypt'  => Crypt::encrypt($sekolah->id_sek),
                  'id_info_crypt' => Crypt::encrypt($dat->id),
                ];
                $urut++;
            }
            if(!sizeOf($data)) $datas = '';
            $success = true; $message = '';
        }else{
            $success = false; $message = '';
            $datas = '';
        }

        return response()->json([
          'success'  => $success,
          'message' => $message,
          'data'  => $datas,
        ]);

    }

    static function HitungPPDBReg($id_ppdb_sek){
        $laki  = DB::table('ta_ppdb_pendaftar')->where('id_ppdb_sek',$id_ppdb_sek)->where('jk',1)->where('status_terima',1)->count();
        $perempuan  = DB::table('ta_ppdb_pendaftar')->where('id_ppdb_sek',$id_ppdb_sek)->where('jk',2)->where('status_terima',1)->count();
        DB::table('ta_ppdb_sek')->where('id',$id_ppdb_sek)->update([
            'laki'  => $laki,
            'perempuan' => $perempuan,
            'total' => $laki + $perempuan,
        ]);
    }

    static function GetDataPPDBKuota($url,$req){
        $success = false; $message = '';
        $exp   = explode('-',$url);
        $ppdb  = DB::table('ta_ppdb_sek')->where('id',$exp[0])->first();
        if(!$ppdb){
            return response()->json([
              'success'  => $success,
              'message' => $message,
              'req' => $req->all()
            ]);
        }

        if(\Request::isMethod('POST')){
            $data  = DB::table('ta_ppdb_kuota')->where('id_ppdb_sek',$ppdb->id)->first();
            if($data){
                DB::table('ta_ppdb_kuota')->where('id',$data->id)->update([
                    'zonasi'  => $req->zonasi,
                    'afirmasi'  => $req->afirmasi,
                    'prestasi'  => $req->prestasi,
                    'perpindahan'  => $req->perpindahan,
                    'kuota'  => $req->kuota,
                ]);
                $message = 'Sukses Update Data';
            }else{
                DB::table('ta_ppdb_kuota')->insert([
                    'id_ppdb_sek' => $ppdb->id,
                    'zonasi'  => $req->zonasi,
                    'afirmasi'  => $req->afirmasi,
                    'prestasi'  => $req->prestasi,
                    'perpindahan'  => $req->perpindahan,
                    'kuota'  => $req->kuota,
                ]);
                $message = 'Sukses Tambah Data';
            }
            $success = true;
            return response()->json([
              'success'  => $success,
              'message' => $message,
              'req' => $req->all()
            ]);
        }

        $dat  = DB::table('ta_ppdb_kuota')->where('id_ppdb_sek',$ppdb->id)->first();
        if($dat){
            $datas = [
              'id'  => $dat->id,
              'zonasi'  => $dat->zonasi,
              'afirmasi'  => $dat->afirmasi,
              'prestasi'  => $dat->prestasi,
              'perpindahan'  => $dat->perpindahan,
              'kuota'  => $dat->kuota,
            ];
            $count = 1;
            $success = true;
        }else{
            $count = 1;
            $success = true;
            $datas = [
              'id'  => 0,
              'zonasi'  => 0,
              'afirmasi'  => 0,
              'prestasi'  => 0,
              'perpindahan'  => 0,
              'kuota'  => 0,
            ];
        }

        return response()->json([
          'success'  => $success,
          'message' => $message,
          'data'  => $datas,
          'count' => $count
        ]);

    }

    static function GuestGetKuotaPPDB($req){
        $keterangan  = []; $success = true; $message = '';
        $info   = DB::table('ta_ppdb_sek')->where('status',1)->where('id_inst',1)
                  ->where('id_sek',$req->id_sek)->where('id_thn',$req->id_thn)->first();
        if($info){
            $dat = DB::table('ta_ppdb_kuota')->where('id_ppdb_sek',$info->id)->first();
            $sek = DB::table('ta_sekolah')->where('id',$info->id_sek)->first();
            if($dat){
                $date  = date('Y-m-d');
                $total = $dat->zonasi + $dat->afirmasi + $dat->prestasi + $dat->perpindahan + $dat->kuota;
                $pendaftar  = DB::table('ta_ppdb_pendaftar')->where('id_ppdb_sek',$info->id)->where('status_terima',1)
                              ->whereDate('created_at',$date)->count();
                $total_pen  = DB::table('ta_ppdb_pendaftar')->where('id_ppdb_sek',$info->id)->count();

                $sisa  = $total - $pendaftar;
                $data  = [
                  'id'  => $dat->id,
                  'zonasi'  => $dat->zonasi,
                  'afirmasi'  => $dat->afirmasi,
                  'prestasi'  => $dat->prestasi,
                  'perpindahan'  => $dat->perpindahan,
                  'kuota'  => $dat->kuota,
                  'total'  => $total,
                  'nm_sek'  => $sek->nama,
                  'sisa'  => $sisa,
                  'terima'  => $pendaftar
                ];
                $keterangan[] = 'Total Kuota Penerimaan '.$sek->nama.' : '.$total;
                $keterangan[] = 'Jumlah yang sudah mendaftar '.$sek->nama.' : '.$total_pen;
                $keterangan[] = '';
                $keterangan[] = 'Jumlah Kuota Penerimaan hari ini : '.$total;
                $keterangan[] = 'Jumlah yang sudah mendaftar '.$sek->nama.' hari ini : '.$pendaftar;
                $harian  = $total - $pendaftar;
                $keterangan[] = 'Sisa Kuota '.$sek->nama.'  hari ini : '.$harian;
            }else{
                $data  = [
                  'id'  => 0,
                  'zonasi'  => 0,
                  'afirmasi'  => 0,
                  'prestasi'  => 0,
                  'perpindahan'  => 0,
                  'kuota'  => 0,
                  'total' => 0,
                  'nm_sek'  => '',
                  'sisa'  => 0,
                  'terima'  => 0
                ];
            }
        }else{
            $keterangan[] = 'Tidak Ada Kuota Penerimaan ';
            $data  = [
              'id'  => 0,
              'zonasi'  => 0,
              'afirmasi'  => 0,
              'prestasi'  => 0,
              'perpindahan'  => 0,
              'kuota'  => 0,
              'total' => 0,
              'nm_sek'  => '',
              'sisa'  => 0,
              'terima'  => 0
            ];
        }
        $keterangan[] = '';
        $keterangan[] = 'Waktu Server : '.date('Y-d-m h:i:s');

        return response()->json([
          'success'  => $success,
          'message' => $message,
          'req'=>$req->all(),
          'data'=>$data,
          'keterangan'  => $keterangan,
        ]);

    }


}
