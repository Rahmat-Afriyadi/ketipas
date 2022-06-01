<?php

namespace App\Http\Controllers\PPDB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Berita;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Config\ConfigController as Config;
use App\Http\Controllers\Admin\HAController as HA;

class PPDBController extends Controller
{
    static function GetJadwal($req){
        $status = true; $message = 'Success Get GetJadwal';
        $id_inst = 1;
        $info   = DB::table('ta_ppdb_info')->where('status',1)->where('id_inst',$id_inst)->first();
        if($info){
            $data  = DB::table('ta_ppdb_jadwal')->where('id_thn',$req->id_thn)->where('id_info',$info->id)->orderBy('ref_jadwal')->get();
            $no = 1;
            foreach($data as $dat){
                $awal   = Config::getFormatHari($dat->awal).', '.Config::chFormatTanggal($dat->awal);
                $akhir   = Config::getFormatHari($dat->akhir).', '.Config::chFormatTanggal($dat->akhir);
                $datas[]  = [
                  'id'  => $dat->id,
                  'urut'  => $no,
                  'uraian'  => $dat->uraian,
                  'awal'  => $awal,
                  'akhir'  => $akhir,
                ];
                $no++;
            }
            if(!sizeOf($data)) $datas = '';
            $nm_pel = $info->nm_pel;
            $status = true;
            $message = 'Data Ditemukan';
        }else{
            $datas  = '';
            $nm_pel = 'Instansi Tidak Ditemukan';
            $status = false;
            $message = 'Data Tidak Ditemukan';
        }

        return response()->json([
          'status'  => $status,
          'message' => $message,
          'data'  => $datas,
          'nm_pel'  => $nm_pel,
        ]);
    }

    static function GetDataInformasi(){
        $id_user  = Auth::id();
        $status = false; $message = 'Gagal Get Data';
        $opr  = DB::table('ta_instansi_opr')->where('id_user',$id_user)->get();
        if(sizeOf($opr)){
            foreach($opr as $datO){
                $idinst[]  = $datO->id;
            }
            $no = 1;
            $data  = DB::table('ta_ppdb_info')->whereIn('id_inst',$idinst)->get();
            foreach($data as $dat){
                if($dat->status) $ur_st = 'Aktif';
                else $ur_st = 'Tidak Aktif';
                $datas[]  = [
                  'id'  => $dat->id,
                  'id_inst'  => $dat->id_inst,
                  'nm_pel'  => $dat->nm_pel,
                  'tahun'   => $dat->tahun,
                  'thn_ajar'  => $dat->thn_ajar,
                  'status'  => $dat->status,
                  'ur_st' => $ur_st,
                  'no'  => $no,
                ];
                $no++;
            }
            if(!sizeOf($data)) $datas = [];
            $success = true; $message = 'Sukses Get Data';
        }else{
            $datas = [];
            $otoritas = 0;
        }

        return response()->json([
          'success'  => $success,
          'message' => $message,
          'data'=>$datas,
        ]);

    }

    static function GedDataPelaksana(){
        $id_user  = Auth::id(); $success = false; $message = '';
        $operator  = DB::table('ta_instansi_opr')->where('id_user',$id_user)->where('status',1)->first();
        if($operator){
            $data  = DB::table('ta_instansi')->where('id',$operator->id)->get();
            foreach($data as $dat){
                $datas[]  = [
                  'id'  => $dat->id,
                  'nama'  => $dat->nama,
                ];
            }
            if(!sizeOf($data)) $datas = '';
            $otoritas = 1; $success = true; $message = 'Sukses Get Data';
        }else{
            $datas = []; $otoritas = 0;
        }

        return response()->json([
          'success'  => $success,
          'message' => $message,
          'data'=>$datas,
          'otoritas'  => $otoritas,
        ]);

    }

    static function TambahDataInformasi($req){
        $success = false; $message = 'Gagal Tambah Data';
        $nm_inst  = DB::table('ta_instansi')->where('id',$req->id_inst)->value('nama');
        $inst = DB::table('ta_ppdb_info')->insert([
          'id_inst'  => $req->id_inst,
          'nm_inst'  => $nm_inst,
          'nm_pel'  => $nm_inst,
          'tahun'   => $req->tahun,
          'thn_ajar'  => $req->thn_ajar,
          'status'  => 0
        ]);

        if($inst){
            $success = true; $message = 'Sukses Tambah Data';
        }

        return response()->json([
          'success'  => $success,
          'message' => $message,
        ]);

    }

    static function EditDataInformasi($url,$req){
        $success = false; $message = 'Gagal Edit Data';
        $exp  = explode('-',$url);
        $dat  = DB::table('ta_ppdb_info')->where('id',$exp[0])->first();
        if($dat){
            if(\Request::isMethod('POST')){
                DB::table('ta_ppdb_info')->where('id',$exp[0])->update([
                    'status'  => $req->status,
                    'tahun' => $req->tahun,
                    'thn_ajar' => $req->thn_ajar,
                ]);
            }
            $success = true; $message = 'Sukses Edit Data';
        }
        return response()->json([
          'success'  => $success,
          'message' => $message,
          'data'  => $dat,
          'id'  => $exp[0],
          'req' => $req->all()
        ]);
    }

    static function HapusDataInformasi($req){
        $del  = DB::table('ta_ppdb_info')->where('id',$req->id_)->delete();
        if($del){
            $success = true; $message = 'Berhasil Hapus Data';
        }else{
            $success = false; $message = 'Gagal Hapus Data';
        }
        return response()->json([
          'success'  => $success,
          'message' => $message,
          'data'  => self::GetDataInformasi(),
          'req' => $req->all(),
        ]);
    }

    static function GetInformasiAturan($url,$req){
        $success = false; $message = 'Gagal Get Informasi Aturan';
        $exp   = explode('-',$url);
        $data  = DB::table('ta_ppdb_aturan')->where('id_info',$exp[0])->first();
        if($data){
            $datas  = [
              'id_info'   => $data->id_info,
              'ket_umum'  => $data->ket_umum,
              'persyaratan' => $data->persyaratan,
              'daya_tampung' => $data->daya_tampung,
              'tahap' => $data->tahap,
              'dasar' => $data->dasar,
              'daftar_ulang' => $data->daftar_ulang,
            ];
            $success = true;
        }else{
            $datas  = '';
        }

        $datinfo  = DB::table('ta_ppdb_info')->select('thn_ajar','tahun')->where('id',$exp[0])->first();

        return response()->json([
          'success'  => $success,
          'message' => $message,
          'data'  => $datas,
          'info'  => $datinfo,
          'req' => $req->all(),
        ]);

    }

    static function FilSekByTA($req){
        $success = false; $message = 'Gagal'; $datas = []; $id_user = Auth::id();
        $admin  = DB::table('users')->where('id',$id_user)->where('admin',1)->where('status',1)->count();
        if(!$admin){
            $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
            if($opr){
                $data  = DB::table('ta_ppdb_sek')->where('id_sek',$opr->id_sek)->where('tahun',$req->thn_ajar)->get();
                foreach($data as $dat){
                    $datas[]  = [
                      'id'  => $dat->id,
                      'nama'  => $dat->nm_sek,
                    ];
                }
                if(!sizeOf($data)) $datas = [];
                $success = true;
            }
        }else{
            $data  = DB::table('ta_ppdb_sek')->where('tahun',$req->thn_ajar)->get();
            foreach($data as $dat){
                $datas[]  = [
                  'id'  => $dat->id,
                  'nama'  => $dat->nm_sek,
                ];
            }
            if(!sizeOf($data)) $datas = [];
            $success = true;
        }

        return response()->json([
          'success'  => $success,
          'message' => $message,
          'data'  => $datas,
          'req' => $req->all(),
        ]);
    }


    static function FilKecByTA($req){
        if($req->id_jenjang){
            $jenjang = DB::table('ref_jenjang_sek')->where('id',$req->id_jenjang)->value('uraian');
        }else{
            $jenjang = '';
        }

        $query  = DB::table('ta_sekolah')->select('id');
        if($req->id_kec) $query->where('id_kec',$req->id_kec);
        if($jenjang) $query->where('jenjang',$jenjang);
        $data   = $query->get();
        foreach($data as $dat){
            $id_sek[]  = $dat->id;
        }
        if(!sizeOf($data)) $id_sek = [0];

        $urut = 1;
        $data  = DB::table('ta_ppdb_sek')->where('tahun',$req->id_thn)->whereIn('id_sek',$id_sek)->get();
        foreach($data as $dat){
            $laki  = 0; $perempuan = 0; $total = 0;
            $reg_l = 0; $reg_p = 0; $tolak_l = 0; $tolak_p = 0;
            $query  = DB::table('ta_ppdb_pendaftar')->select('jk','status_terima')->where('id_ppdb_sek',$dat->id);
            if($req->id_jalur) $query->where('jalur',$req->id_jalur);
            $cek    = $query->get();
            if(sizeOf($cek)){
                foreach($cek as $dcek){
                    if($dcek->status_terima == 2){
                        // diterima
                        if($dcek->jk == 1) $laki++;
                        else $perempuan++;
                    }if($dcek->status_terima == 3){
                        // ditolak
                        if($dcek->jk == 1) $tolak_l++;
                        else $tolak_p++;
                    }
                    if($dcek->jk == 1) $reg_l++;
                    else $reg_p++;
                }
                $total = $laki + $perempuan;

            }

            $datas[]  = [
              'urut'  => $urut,
              'nama'  => $dat->nm_sek,
              'alamat'  => $dat->alamat,
              'jumlah'  => $total,
              'laki'  => $laki,
              'perempuan' => $perempuan,
              'reg_l' => $reg_l,
              'reg_p' => $reg_p,
              'tolak_l' => $tolak_l,
              'tolak_p' => $tolak_p,

            ];
            $urut++;
        }
        if(!sizeOf($data)) $datas = [];

        $success = true; $message = 'Sukses Get Data';
        return response()->json([
          'success'  => $success,
          'message' => $message,
          'data'  => $datas,
          'req' => $req->all(),
        ]);
    }

}
