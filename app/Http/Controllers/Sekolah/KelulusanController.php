<?php

namespace App\Http\Controllers\Sekolah;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use View;
use Str;
use App\Models\Ta_Siswas;
use App\Models\Ta_Sekolah;
use App\Models\Ta_Sekolah_Opr;
use App\Models\Ta_Kelas;
use App\Models\Ta_Rombel;
use App\Http\Controllers\Admin\HAController as HA;


class KelulusanController extends Controller{

    static function GetData($req){

        $tahun   = DB::table('ref_tahun')->where('id',$req->ta)->value('tahun');
        $siswa = DB::table('ta_kelulusan')->where('id',$req->id_kel)->first();
        $jenjang   = DB::table('ta_sekolah')->where('id',$req->id_sek)->value('jenjang');
        if($jenjang){
            $mapel  = DB::table('ref_mapel')->where('jenjang',$jenjang)->where('status',1)->get();
            foreach($mapel as $dat){
                $cek = DB::table('ta_kelulusan_nilai')->where('id_mapel',$dat->id)->where('tahun',$tahun)->where('id_sek',$req->id_sek)->where('nisn',$req->nisn)->count();
                if(!$cek){
                    DB::table('ta_kelulusan_nilai')->insert([
                        'tahun' => $tahun,
                        'id_sek'  => $siswa->id_sek,
                        'id_mapel'  => $dat->id,
                        'nm_mapel'  => $dat->uraian,
                        'nisn'  => $siswa->nisn,
                        'nama'  => $siswa->nama,
                        'nilai' => 0,
                        'updated_by'  => Auth::user()->name,
                    ]);
                }
            }
        }
        $urut  = 1;
        $data  = DB::table('ta_kelulusan_nilai')->where('tahun',$tahun)->where('id_sek',$siswa->id_sek)->where('nisn',$siswa->nisn)->get();
        foreach($data as $dat){
            $datas[] = [
              'id'  => $dat->id,
              'mapel'  => $dat->nm_mapel,
              'nilai'  => $dat->nilai,
              'urut'  => $urut,
            ];
            $urut++;
        }
        if(!sizeOf($data)) $datas = [];

        return response()->json([
            'success'  => true,
            'message' => 'Get Data Kelulusan',
            'data'  => $datas,
        ]);

    }

    static function TambahData($url,$req){
        $error = 0; $message = '';
        $cek  = DB::table('ta_kelulusan_nilai')->where('tahun',$req->ta)->where('id_sek',$req->id_sek)
                ->where('id_mapel',$req->id_mapel)->where('nisn',$req->nisn)->first();
        $mapel = DB::table('ref_mapel')->where('id',$req->id_mapel)->first();
        $siswa = DB::table('ta_kelulusan')->where('nisn',$req->nisn)->where('id_sek',$req->id_sek)
                 ->where('tahun',$req->ta)->first();
        if(!$cek){
            DB::table('ta_kelulusan_nilai')->insert([
                'tahun' => $req->ta,
                'id_sek'  => $req->id_sek,
                'id_mapel'  => $req->id_mapel,
                'nm_mapel'  => $mapel->uraian,
                'nisn'  => $req->nisn,
                'nama'  => $siswa->nama,
                'nilai' => $req->nilai
            ]);
        }else{
            $error = 1; $message = 'Data '.$mapel->uraian.' Sudah Ada';
        }
        return [
          'req' => $req->all(),
          'ket' => 'tambah data kel',
          'cek' => $cek,
          'error' => $error,
          'pesan' => $message,
        ];
    }

    static function HapusData($url,$req){
        DB::table('ta_kelulusan_nilai')->where('id',$req->id_)->delete();
        return [
          'req' => $req->all()
        ];
    }

    static function UpdateKelulusan($req){
        $success = false; $message = 'Gagal Update Data Kelulusan, Data Tidak Ditemukan';
        $cek   = DB::table('ta_kelulusan')->where('id',$req->id_)->first();
        if($cek){
            DB::table('ta_kelulusan')->where('id',$req->id_)->update([
                'is_lulus'  => $req->lulus,
            ]);
            $success = true; $message = 'Data Kelulusan Berhasil Diupdate';
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'req' => $req->all()
        ]);
    }

    static function UpdateNilai($req){
        $success = false; $message = 'Gagal Update Data Kelulusan, Data Tidak Ditemukan';
        $cek  = DB::table('ta_kelulusan_nilai')->where('id',$req->id_)->first();
        if($cek){
            DB::table('ta_kelulusan_nilai')->where('id',$req->id_)->update([
                'nilai' => $req->nilai,
                'updated_by'  => Auth::user()->name,
            ]);
            $success = true; $message = 'Sukses Update Nilai';
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'req' => $req->all()
        ]);
    }

}
