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

use App\Http\Controllers\Config\ConfigController as Config;

class JadwalController extends Controller{

    static function GetJadwal($req){
        $info   = DB::table('ta_ppdb_info')->where('status',1)->where('id_inst',$req->id_inst)->first();
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
        }else{
            $datas  = '';
            $nm_pel = 'Instansi Tidak Ditemukan';
        }
        return ['data'=>$datas, 'nm_pel'=>$nm_pel];
    }

    static function GetApiJadwal($url,$req){
      $success = false; $message = 'Get Jadwal';
      $exp   = explode('-',$url);
      $data  = DB::table('ta_ppdb_jadwal')->where('id_info',$exp[0])->get();
      if(sizeOf($data)){
          foreach($data as $dat){
              $datas[]  = [
                'id'  => $dat->id,
                'uraian'  => $dat->uraian,
                'model'  => $dat->model,
                'awal'  => $dat->awal,
                'akhir' => $dat->akhir
              ];
          }
          $ref = 0;
          $success = true;
      }else{
          $data  = DB::table('ref_ppdb_jadwal')->where('status',1)->get();
          foreach($data as $dat){
              $datas[]  = [
                'id'  => $dat->id,
                'uraian'  => $dat->uraian,
                'model'  => $dat->model,
                'awal'  => date('Y-m-d'),
                'akhir' => date('Y-m-d')
              ];
          }
          $ref = 1;
          $success = true;
      }

      return response()->json([
        'success'  => $success,
        'message' => $message,
        'data'  => $datas,
        'ref'  => $ref,
        'req' => $req->all(),
      ]);

    }

    static function TambahApiJadwal($req){
        $id_info  = $req->id_info;
        $bulan  = ['Jan'=>'01','Feb'=>'02','Mar'=>'03', 'Apr'=>'04', 'May'=>'05', 'Jun'=>'06', 'Jul'=>'07', 'Aug'=>'08', 'Sep'=>'09', 'Oct'=>'10', 'Nov'=>'11', 'Dec'=>'12'];
        foreach ($req->all() as $item => $value) {
            $exp  = explode("-", $item);
            if(sizeOf($exp) > 1){
                $akhir  = $value;
                $arr = explode(' ', $akhir);
                if(sizeOf($arr) > 1){
                  $akhir = $arr[3].'-'.$bulan[$arr[1]].'-'.$arr[2];
                }
                $model  = $exp[0];

                $ref  = DB::table('ref_ppdb_jadwal')->where('model',$model)->where('status',1)->first();
                if($ref){
                    $jadwal  = DB::table('ta_ppdb_jadwal')->where('id_info',$id_info)->where('ref_jadwal',$ref->id)->where('model',$model)->first();
                    if($jadwal){
                        DB::table('ta_ppdb_jadwal')->where('id',$jadwal->id)->update([
                            'akhir' => $akhir,
                        ]);
                    }else{
                        DB::table('ta_ppdb_jadwal')->insert([
                            'id_info' => $id_info,
                            'ref_jadwal'  => $ref->id,
                            'uraian'  => $ref->uraian,
                            'model' => $model,
                            'akhir' => $akhir,
                        ]);
                    }
                }

            }else{
                $awal  = $value;
                $arr = explode(' ', $awal);
                if(sizeOf($arr) > 1){
                  $awal = $arr[3].'-'.$bulan[$arr[1]].'-'.$arr[2];
                }
                $model = $item;

                $ref  = DB::table('ref_ppdb_jadwal')->where('model',$model)->where('status',1)->first();
                if($ref){
                        $jadwal  = DB::table('ta_ppdb_jadwal')->where('id_info',$id_info)->where('ref_jadwal',$ref->id)->where('model',$ref->model)->first();
                        if($jadwal){
                            DB::table('ta_ppdb_jadwal')->where('id',$jadwal->id)->update([
                                'awal'  => $awal,
                            ]);
                        }else{
                            DB::table('ta_ppdb_jadwal')->insert([
                                'id_info' => $id_info,
                                'ref_jadwal'  => $ref->id,
                                'uraian'  => $ref->uraian,
                                'model' => $ref->model,
                                'awal'  => $awal,
                            ]);
                        }

                        $datas[] = $ref->model;
                }
            }

        }

        return ['error'=>0, 'pesan'=>'Sukses Tambah', 'data'=>$datas ];
    }

    static function chekJadwalDaftar($req){
        $data   = ''; $keterangan [] = ''; $pesan = '';
        $tahun  = DB::table('ref_tahun')->where('id',$req->id_thn)->value('tahun');
        $info   = DB::table('ta_ppdb_info')->where('tahun',$tahun)->where('id_inst',$req->id_inst)->first();
        if($info){
            $jadwal = DB::table('ta_ppdb_jadwal')->where('id_thn',$req->id_thn)->where('id_info',$info->id)->where('ref_jadwal',1)->first();
            if($jadwal){
                $j_awal   = '06:00:00';
                $j_akhir  = '12:00:00';
                $j_now    = date('h:i:s');
                $now      = date('Y-m-d h:i:s');
                $awal     = $jadwal->awal;
                $akhir    = $jadwal->akhir;
                $awal    = strtotime($awal) - strtotime($now);
                $akhir   = strtotime($akhir) - strtotime($now);
                if($awal < 1){
                    if($akhir >= 0){
                        $jawal   = strtotime($j_awal) - strtotime($now);
                        $jakhir  = strtotime($j_akhir) - strtotime($now);
                        if($jawal < 1){
                            if($jakhir >= 0){
                                $status = 1;
                                $pesan  = ''.$now;
                                $pesan  .= ' '.$awal;
                                $pesan  .= ' '.$akhir;
                                $pesan  .= ' '.$jadwal->id.' '.$jadwal->awal;
                                $keterangan[]  = 'Silahkan Pilih Jenjang dan Jalur Pendaftaran';
                                $keterangan[]  = 'Jadwal Pendaftaran Dimulai Pukul '.$j_awal.' s/d '.$j_akhir;
                                $keterangan[]  = 'Waktu Server : '.$now;

                            }else{
                                $status = 0;
                                $keterangan[]  = 'Jam Pendaftaran Hari sudah selesai';
                                $keterangan[]  = 'Tanggal Pendaftaran : '.$jadwal->awal.' s/d '.$jadwal->akhir;
                                $keterangan[]  = 'Jadwal Pendaftaran Dimulai Pukul '.$j_awal.' s/d '.$j_akhir;
                                $keterangan[]  = 'Waktu Server : '.$now;
                            }
                        }else{
                            $status = 0;
                            $keterangan[]  = 'Jam Pendaftaran Hari ini Belum Dimulai';
                            $keterangan[]  = 'Tanggal Pendaftaran : '.$jadwal->awal.' s/d '.$jadwal->akhir;
                            $keterangan[]  = 'Jadwal Pendaftaran Dimulai Pukul '.$j_awal.' s/d '.$j_akhir;
                            $keterangan[]  = 'Waktu Server : '.$now;
                        }

                    }else{
                        $status = 0;
                        $keterangan[]  = 'Tahapan Pendaftaran Sudah Selesai.';
                        $keterangan[]  = 'Tanggal Pendaftaran : '.$jadwal->awal.' s/d '.$jadwal->akhir;
                        $keterangan[]  = 'Jadwal Pendaftaran Dimulai Pukul '.$j_awal.' s/d '.$j_akhir;
                        $keterangan[]  = 'Waktu Server : '.$now;
                    }
                }else{
                    $status = 0;
                    $keterangan[]  = 'Tahapan Pendaftaran Belum Dimulai.';
                    $keterangan[]  = 'Tanggal Pendaftaran : '.$jadwal->awal.' s/d '.$jadwal->akhir;
                    $keterangan[]  = 'Jadwal Pendaftaran Dimulai Pukul '.$j_awal.' s/d '.$j_akhir;
                    $keterangan[]  = 'Waktu Server : '.$now;
                }
            }else{
                $status = 0;
                $keterangan[]  = 'Belum ada Tahapan Pendaftaran saat ini, Silahkan Lihat Jadwal Pendaftaran';
            }
        }else{
            $status = 0;
            $keterangan[]  = 'Belum ada Tahapan Pendaftaran saat ini, Silahkan Lihat Jadwal Pendaftaran';
        }

        return [
          'status'=>$status, 'pesan'=>$pesan,
          'keterangan'  => $keterangan,
          'req' => $req->all()
        ];
    }

    static function GetBySek($url,$req){
        $status = 0;
        // $status = 1;
        $ppdb   = DB::table('ta_ppdb_sek')->where('id',$req->id_)->where('pelaksanaan',0)->where('status',1)->first();
        if($ppdb){
            $now      = date('Y-m-d');
            $awal     = $ppdb->awal;
            $akhir    = $ppdb->akhir;
            $awal    = strtotime($awal) - strtotime($now);
            $akhir   = strtotime($akhir) - strtotime($now);
            if($awal < 1 && $akhir >= 0){
                $status  = 1;
            }
        }
        return [
          'url' => $url,
          'req' => $req->all(),
          'status'  => $status,
          'tes' => $req->id_
        ];
    }


}
