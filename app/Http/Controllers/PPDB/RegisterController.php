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
use Image;
use Crypt;
use File;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Config\ConfigController as Config;
use App\Http\Controllers\PPDB\AturanController as Aturan;
use App\Http\Controllers\Admin\HAController as HA;

class RegisterController extends Controller {

    static function RegisterPPDB($req){
        if(\Request::isMethod('POST')){

            $cek_nik     = DB::table('ta_ppdb_pendaftar')->where('nik',$req->siswa_nik)->count();
            if($cek_nik){
                $pesan_error[]  = 'Maaf, NIK Siswa Sudah Teregister';
                return response()->json([
                    'success'  => false,
                    'pesan_error' => $pesan_error,
                    'message'=>'Gagal Register, Silahkan Lengkapi Pesan Error'
                ]);
            }

            $ppdb  = DB::table('ta_ppdb_sek')->where('id_thn',$req->id_thn)->where('id_sek',$req->tujuan_id_sek)->first();
            if(!$ppdb){
                $pesan_error[]  = 'Terjadi Kesalahan, Hubungi Administrator (39)';
                return response()->json([
                    'success'  => false,
                    'pesan_error' => $pesan_error,
                    'message'=>'Gagal Register, Silahkan Lengkapi Pesan Error'
                ]);
            }

            $no_peserta  = rand(999,10000);
            $no_peserta = DB::table('ta_ppdb_pendaftar')->select('no_peserta')->orderBy('no_peserta','DESC')->value('no_peserta');
            $no_peserta++;
            if(isset($req->siswa_no_usbn)) $no_usbn = $req->siswa_no_usbn;
            else $no_usbn = 0;

            $bulan  = ['Jan'=>'01','Feb'=>'02','Mar'=>'03', 'Apr'=>'04', 'May'=>'05', 'Jun'=>'06', 'Jul'=>'07', 'Aug'=>'08', 'Sep'=>'09', 'Oct'=>'10', 'Nov'=>'11', 'Dec'=>'12'];
            //  Tue Jun 02 2020 10:45:00 => format pilihan langsung
            //  2020-06-02T03:45:00.000Z => format ambil dari storage

            $strip = substr($req->siswa_tgl_lhr,4,1);
            if($strip == '-'){
                $tgl_lhr = substr($req->siswa_tgl_lhr,0,10);
            }else{
                $arr = explode(' ', $req->siswa_tgl_lhr);
                if(sizeOf($arr) > 1){
                  $tgl_lhr = $arr[3].'-'.$bulan[$arr[1]].'-'.$arr[2];
                }
            }

            // return ['error'=>1, 'pesan'=>'Input Tanggal Lahir', 'data'=>$tgl_lhr, 'req'=>$req->siswa_tgl_lhr ];
            $id = 0;
            $id  = DB::table('ta_ppdb_pendaftar')->insertGetId([
                'id_inst' => $req->id_inst,
                'id_sek'  => $req->tujuan_id_sek,
                'id_ppdb_sek' => $ppdb->id,
                'no_peserta'  => $no_peserta,
                'no_usbn'  => $no_usbn,
                'nama'  => $req->siswa_nama,
                'nik' => $req->siswa_nik,
                'jk'  => $req->siswa_jk,
                'tempat_lhr'  => $req->siswa_tempat_lhr,
                'tgl_lhr'  => $tgl_lhr,
                'alamat'  => $req->siswa_alamat,
                'nm_bpk'  => $req->ortu_nama,
                'nik_bpk'  => $req->ortu_nik,
                'hp_bpk'  => $req->ortu_hp_bpk,
                'alamat_bpk'  => $req->ortu_alamat,
                'nm_ibu'  => $req->ortu_nm_ibu,
                'nik_ibu'  => $req->ortu_nik_ibu,
                'asal_sek'  => $req->asal_nama,
                'no_sttb'  => $req->asal_no_sttb,
                'nilai_skhun'  => $req->asal_skhun,
                'asal_alamat'  => $req->asal_alamat,
                'thn_lulus'  => $req->asal_thn_lulus,
                'jalur'  => $req->id_jalur,
            ]);

            $dat  = DB::table('ta_ppdb_pendaftar')->where('id',$id)->first();
            if($dat){
                $sek  = DB::table('ta_sekolah')->where('id',$dat->id_sek)->first();
                $data  = [
                  'no_peserta'  => $dat->no_peserta,
                  'nama'  => $dat->nama,
                  'tempat_lhr'  => $dat->tempat_lhr,
                  'tgl_lhr' => $dat->tgl_lhr,
                  'sek_tujuan'  => $sek->nama,
                  'id_'  => Crypt::encrypt($dat->id)
                ];
                return response()->json([
                    'success'  => true,
                    'pesan_error' => [],
                    'message'=>'Sukses Register PPDB',
                    'data'  => $data,
                ]);
            }else{
                return response()->json([
                    'success'  => false,
                    'pesan_error' => [],
                    'message'=>'Gagal Register Peserta',
                    'data'  => []
                ]);
            }

        }
    }

    static function Registered($req){
        $otoritas  = HA::GetOtoritas(Auth::id(),2);
        $id_user = Auth::id();
        $admin  = DB::table('users')->where('id',$id_user)->where('admin',1)->where('status',1)->count();
        if($admin){
            $sek  = DB::table('ta_ppdb_sek')->where('id',$req->id_ppdb_sek)->first();
            if(!$sek){
                return response()->json([
                    'success'  => false,
                    'message' => 'Data PPDB Tidak Ditemukan',
                ]);
            }

            $urut = 1;
            $status_terima  = $req->status_terima;
            $query  = DB::table('ta_ppdb_pendaftar')->where('id_ppdb_sek',$sek->id);
            if($status_terima) $query->where('status_terima',$status_terima);
            if($req->jalur) $query->where('jalur',$req->jalur);
            $data  = $query->orderBy('created_at')->get();
            foreach($data as $dat){
                if($dat->nisn == 0){
                    $cek = DB::table('dapodik_siswa_akhir')->where('nik',$dat->nik)->first();
                    if($cek){
                        DB::table('ta_ppdb_pendaftar')->where('id',$dat->id)->update([
                            'nisn'  => $cek->nisn,
                        ]);
                    }
                }elseif($dat->nik == 0){
                    $cek = DB::table('dapodik_siswa_akhir')->where('nisn',$dat->nisn)->first();
                    if($cek){
                        DB::table('ta_ppdb_pendaftar')->where('id',$dat->id)->update([
                            'nik'  => $cek->nik,
                        ]);
                    }
                }

                $id_jenjang  = DB::table('ta_sekolah')->where('id',$sek->id_sek)->value('jenjang');
                if($dat->jk == 1) $jk = 'Laki - Laki';
                else $jk = 'Perempuan';
                if($dat->jalur == 1) $ur_jalur = 'Zonasi';
                elseif($dat->jalur == 2) $ur_jalur = 'Afirmasi';
                elseif($dat->jalur == 3) $ur_jalur = 'Prestasi';
                elseif($dat->jalur == 4) $ur_jalur = 'Perpindahan Orang Tua';
                else $ur_jalur = '';
                $datas[]  = [
                  'id'  => $dat->id,
                  'id_' => Crypt::encrypt($dat->id),
                  'nama'  => $dat->nama,
                  'nik'  => $dat->nik,
                  'nisn'  => $dat->nisn,
                  'nm_bpk'  => $dat->nm_bpk,
                  'nik_bpk'  => $dat->nik_bpk,
                  'nm_ibu'  => $dat->nm_ibu,
                  'nik_ibu' => $dat->nik_ibu,
                  'alamat_bpk'  => $dat->alamat_bpk,
                  'hp_bpk'  => $dat->hp_bpk,
                  'tempat_lhr'  => $dat->tempat_lhr,
                  'tgl_lhr'  => $dat->tgl_lhr,
                  'alamat'  => $dat->alamat,
                  'asal_sek'  => $dat->asal_sek,
                  'asal_alamat'  => $dat->asal_alamat,
                  'thn_lulus'  => $dat->thn_lulus,
                  'id_jenjang'  => $id_jenjang,
                  'urut'  => $urut,
                  'ur_jk'  => $jk,
                  'status_terima'  => $dat->status_terima,
                  'ket_status'  => $dat->ket_status,
                  'jalur'  => $dat->jalur,
                  'ur_jalur'  => $ur_jalur,
                ];
                $urut++;
            }
            if(!sizeOf($data)) $datas = [];



            return response()->json([
                'success'  => true,
                'message' => 'Data PPDB Ditemukan',
                'data'  => $datas,
                'req' => $req->all()
            ]);

        }
        if($otoritas['id_sek']){
            $sek  = DB::table('ta_ppdb_sek')->where('id',$req->id_ppdb_sek)->first();
            if(!$sek) return ['data'=>[]];

            // cek otoritas operator
            $cek  = DB::table('ta_sekolah_opr')->where('id_sek',$sek->id_sek)->where('id_user',Auth::id())->where('status',1)->first();
            if(!$cek){
                return response()->json([
                    'success'  => false,
                    'message' => 'Gagal Otoritas',
                ]);
            }

            $urut = 1;
            $status_terima  = $req->status_terima;
            $query  = DB::table('ta_ppdb_pendaftar')->where('id_ppdb_sek',$sek->id);
            if($status_terima) $query->where('status_terima',$status_terima);
            if($req->jalur) $query->where('jalur',$req->jalur);
            $data  = $query->orderBy('created_at')->get();
            foreach($data as $dat){
                if($dat->nisn == 0){
                    $cek = DB::table('dapodik_siswa_akhir')->where('nik',$dat->nik)->first();
                    if($cek){
                        DB::table('ta_ppdb_pendaftar')->where('id',$dat->id)->update([
                            'nisn'  => $cek->nisn,
                        ]);
                    }
                }elseif($dat->nik == 0){
                    $cek = DB::table('dapodik_siswa_akhir')->where('nisn',$dat->nisn)->first();
                    if($cek){
                        DB::table('ta_ppdb_pendaftar')->where('id',$dat->id)->update([
                            'nik'  => $cek->nik,
                        ]);
                    }
                }

                $id_jenjang  = DB::table('ta_sekolah')->where('id',$sek->id_sek)->value('jenjang');
                if($dat->jk == 1) $jk = 'Laki - Laki';
                else $jk = 'Perempuan';
                if($dat->jalur == 1) $ur_jalur = 'Zonasi';
                elseif($dat->jalur == 2) $ur_jalur = 'Afirmasi';
                elseif($dat->jalur == 3) $ur_jalur = 'Prestasi';
                elseif($dat->jalur == 4) $ur_jalur = 'Perpindahan Orang Tua';
                else $ur_jalur = '';
                $datas[]  = [
                  'id'  => $dat->id,
                  'id_' => Crypt::encrypt($dat->id),
                  'nama'  => $dat->nama,
                  'nik'  => $dat->nik,
                  'nisn'  => $dat->nisn,
                  'nm_bpk'  => $dat->nm_bpk,
                  'nik_bpk'  => $dat->nik_bpk,
                  'nm_ibu'  => $dat->nm_ibu,
                  'nik_ibu' => $dat->nik_ibu,
                  'alamat_bpk'  => $dat->alamat_bpk,
                  'hp_bpk'  => $dat->hp_bpk,
                  'tempat_lhr'  => $dat->tempat_lhr,
                  'tgl_lhr'  => $dat->tgl_lhr,
                  'alamat'  => $dat->alamat,
                  'asal_sek'  => $dat->asal_sek,
                  'asal_alamat'  => $dat->asal_alamat,
                  'thn_lulus'  => $dat->thn_lulus,
                  'id_jenjang'  => $id_jenjang,
                  'urut'  => $urut,
                  'ur_jk'  => $jk,
                  'status_terima'  => $dat->status_terima,
                  'ket_status'  => $dat->ket_status,
                  'jalur'  => $dat->jalur,
                  'ur_jalur'  => $ur_jalur,
                ];
                $urut++;
            }
            if(!sizeOf($data)) $datas = [];
        }else{
            $datas  = [];
        }

        return response()->json([
            'success'  => false,
            'message' => '...',
            'data'=>$datas,
            'otoritas'  => $otoritas,
            'testing' => $otoritas['lihat'],
            'ppdb'  =>  DB::table('ta_ppdb_sek')->where('id',$req->id_ppdb_sek)->first(),
            'req' => $req->all()
        ]);
    }

    static function GetDataRegistered($req){
        $otoritas  = HA::GetOtoritas(Auth::id(),2);
        if($otoritas['id_sek']){
            $sek  = DB::table('ta_ppdb_sek')->where('id',$req->id_ppdb_sek)->first();
            if(!$sek) return ['data'=>[]];

            // cek otoritas operator
            $cek  = DB::table('ta_sekolah_opr')->where('id_sek',$sek->id_sek)->where('id_user',Auth::id())->where('status',1)->first();
            if(!$cek){
                return response()->json([
                    'success'  => false,
                    'message' => 'Gagal Otoritas',
                ]);
            }

            $urut = 1;
            $status_terima  = $req->status_terima;
            $query  = DB::table('ta_ppdb_pendaftar')->where('id_ppdb_sek',$sek->id);
            if($status_terima) $query->where('status_terima',$status_terima);
            if($req->jalur) $query->where('jalur',$req->jalur);
            $data  = $query->orderBy('created_at')->get();
            foreach($data as $dat){
                if($dat->nisn == 0){
                    $cek = DB::table('dapodik_siswa_akhir')->where('nik',$dat->nik)->first();
                    if($cek){
                        DB::table('ta_ppdb_pendaftar')->where('id',$dat->id)->update([
                            'nisn'  => $cek->nisn,
                        ]);
                    }
                }elseif($dat->nik == 0){
                    $cek = DB::table('dapodik_siswa_akhir')->where('nisn',$dat->nisn)->first();
                    if($cek){
                        DB::table('ta_ppdb_pendaftar')->where('id',$dat->id)->update([
                            'nik'  => $cek->nik,
                        ]);
                    }
                }

                $id_jenjang  = DB::table('ta_sekolah')->where('id',$sek->id_sek)->value('jenjang');
                if($dat->jk == 1) $jk = 'Laki - Laki';
                else $jk = 'Perempuan';
                if($dat->jalur == 1) $ur_jalur = 'Zonasi';
                elseif($dat->jalur == 2) $ur_jalur = 'Afirmasi';
                elseif($dat->jalur == 3) $ur_jalur = 'Prestasi';
                elseif($dat->jalur == 4) $ur_jalur = 'Perpindahan Orang Tua';
                else $ur_jalur = '';
                $datas[]  = [
                  'id'  => $dat->id,
                  'id_' => Crypt::encrypt($dat->id),
                  'nama'  => $dat->nama,
                  'nik'  => $dat->nik,
                  'nisn'  => $dat->nisn,
                  'nm_bpk'  => $dat->nm_bpk,
                  'nik_bpk'  => $dat->nik_bpk,
                  'nm_ibu'  => $dat->nm_ibu,
                  'nik_ibu' => $dat->nik_ibu,
                  'alamat_bpk'  => $dat->alamat_bpk,
                  'hp_bpk'  => $dat->hp_bpk,
                  'tempat_lhr'  => $dat->tempat_lhr,
                  'tgl_lhr'  => $dat->tgl_lhr,
                  'alamat'  => $dat->alamat,
                  'asal_sek'  => $dat->asal_sek,
                  'asal_alamat'  => $dat->asal_alamat,
                  'thn_lulus'  => $dat->thn_lulus,
                  'id_jenjang'  => $id_jenjang,
                  'urut'  => $urut,
                  'ur_jk'  => $jk,
                  'status_terima'  => $dat->status_terima,
                  'ket_status'  => $dat->ket_status,
                  'jalur'  => $dat->jalur,
                  'ur_jalur'  => $ur_jalur,
                ];
                $urut++;
            }
            if(!sizeOf($data)) $datas = [];
        }else{
            $datas  = [];
        }

        return response()->json([
            'success'  => false,
            'message' => '...',
            'data'=>$datas,
            'otoritas'  => $otoritas,
            'testing' => $otoritas['lihat'],
            'ppdb'  =>  DB::table('ta_ppdb_sek')->where('id',$req->id_ppdb_sek)->first(),
            'req' => $req->all()
        ]);
    }

    static function PdfRegisterPeserta($url){
        $id  = Crypt::decrypt($url);
        $dat  = DB::table('ta_ppdb_pendaftar')->where('id',$id)->first();
        if(\Request::isMethod('POST')){
            if($dat){
                if($dat->jk == 1) $jk = 'Laki - Laki';
                elseif($dat->jk == 2) $jk = 'Perempuan';
                else $jk = '-';

                $sek   = DB::table('ta_sekolah')->where('id',$dat->id_sek)->first();
                if(!$sek) return '';

                if($dat->jalur == 1) $ur_jalur = 'Zonasi';
                elseif($dat->jalur == 2) $ur_jalur = 'Afirmasi';
                elseif($dat->jalur == 3) $ur_jalur = 'Prestasi';
                elseif($dat->jalur == 4) $ur_jalur = 'Perpindahan Orang Tua';
                else $ur_jalur = '';

                if($sek->jenjang == 'SMP'){
                    $lampiran = [
                      'Bagi siswa yang berprestasi dalam bidang Seni dan Olah Raga melampirkan Sertifikat yang ditanda tangani oleh panitia pelaksana.',
                      'Akte Kelahiran / Surat Keterangan Lahir  ( foto copy  2 lembar )',
                      'Foto copy Kartu Keluarga ( KK )  sebanyak  2 lembar',

                    ];
                    $no_usbn  = $dat->no_usbn;
                }else{
                    $lampiran = [
                      'Akte Kelahiran / Surat Keterangan Lahir  ( foto copy  2 lembar )',
                      'Foto copy Kartu Keluarga ( KK )  sebanyak  2 lembar',
                    ];
                    $no_usbn  = '-';
                }

                $data  = [
                  'id'  => $dat->id,
                  'nama'  => $dat->nama,
                  'nik'  => $dat->nik,
                  'nisn'  => $dat->nisn,
                  'tempat_lhr'  => $dat->tempat_lhr,
                  'tgl_lhr'  => Config::chFormatTanggal($dat->tgl_lhr),
                  'alamat'  => $dat->alamat,
                  'no_peserta'  => $dat->no_peserta,
                  'nm_bpk'  => $dat->nm_bpk,
                  'nik_bpk'  => $dat->nik_bpk,
                  'asal_sek'  => $dat->asal_sek,
                  'no_sttb' => $dat->no_sttb,
                  'no_usbn' => $no_usbn,
                  'asal_alamat' => $dat->asal_alamat,
                  'nilai_skhun'  => $dat->nilai_skhun,
                  'thn_lulus'  => $dat->thn_lulus,
                  'jk'  => $jk,
                  'ur_jalur'  => $ur_jalur,
                ];

                if($sek->logo){
                  $logo = Config::getBucketAWS().$sek->logo;
                }else $logo = '';
                return ['data'=> $data, 'nm_sek'=>$sek->nama, 'alamat_sek'=> $sek->alamat, 'logo'=>$logo , 'lampiran'=>$lampiran];
            }
            return '';
        }
        if($dat){
            $data['id']  = $url;
            $data['title']  = 'Register '.$dat->nama;
            return view('ppdb.pdf.register',$data);
        }
        return 'x';
    }

    static function TolakDataRegister($req){
        $id  = Crypt::decrypt($req->id);
        $data  = DB::table('ta_ppdb_pendaftar')->where('id',$id)->first();
        if($data){
            DB::table('ta_ppdb_pendaftar')->where('id',$id)->update([
              'status_terima' => 2,
            ]);
            $error = 0; $pesan = 'Data Berhasil Dihapus';
        }else{
            $error = 1; $pesan = 'Data Tidak Ditemukan';
        }

        return ['req'=>$req->all(), 'error'=>$error, 'pesan'=>$pesan,];
    }

    static function ExcelRegisterAllPeserta($url){
        $arr = explode('-', $url);
        if(sizeOf($arr) > 1){
            $id_info  = $arr[0];
            $id_sek   = $arr[1];
            $id_info  = Crypt::decrypt($id_info);
            $id_sek   = Crypt::decrypt($id_sek);

            $data['daftar']  = DB::table('ta_ppdb_pendaftar')->where('id_info',$id_info)->where('id_sek',$id_sek)->where('status_terima',1)->orderBy('id')->get();
            $data['url']  = 'penerimaan-peserta-didik-baru';
            $data['title']  = 'PPDB Online Siswa Pendaftar';
            $data['nm_file']  = 'register-siswa';
            return view('ppdb.excel.registered',$data);
        }
        return '';
    }

    static function UpdateRegisterPPDB($req){
        $id  = Crypt::decrypt($req->id_);
        $register  = DB::table('ta_ppdb_pendaftar')->where('id',$id)->first();
        if($register){
            $error  = 0;
            $pesan  = 'Update Sukses';
            DB::table('ta_ppdb_pendaftar_history')->insert([
                'id_ppdb_pendaftar' => $id,
                'id_inst' => $register->id_inst,
                'id_sek'  => $register->id_sek,
                'no_peserta'  => $register->no_peserta,
                'no_usbn'  => $register->no_usbn,
                'nama'  => $register->nama,
                'nik' => $register->nik,
                'jk'  => $register->jk,
                'tempat_lhr'  => $register->tempat_lhr,
                'tgl_lhr'  => $register->tgl_lhr,
                'alamat'  => $register->alamat,
                'nm_bpk'  => $register->nm_bpk,
                'nik_bpk'  => $register->nik,
                'hp_bpk'  => $register->hp_bpk,
                'alamat_bpk'  => $register->alamat,
                'nm_ibu'  => $register->nm_ibu,
                'nik_ibu'  => $register->nik_ibu,
                'asal_sek'  => $register->asal_sek,
                'no_sttb'  => $register->no_sttb,
                'nilai_skhun'  => $register->nilai_skhun,
                'asal_alamat'  => $register->asal_alamat,
                'thn_lulus'  => $register->thn_lulus,
                'jalur'  => $register->jalur,
                'status_terima'  => $register->status_terima,
                'ket_status'  => $register->ket_status,
                'updated_by'  => Auth::id().' - '.Auth::user()->name,
            ]);

            $bulan  = ['Jan'=>'01','Feb'=>'02','Mar'=>'03', 'Apr'=>'04', 'May'=>'05', 'Jun'=>'06', 'Jul'=>'07', 'Aug'=>'08', 'Sep'=>'09', 'Oct'=>'10', 'Nov'=>'11', 'Dec'=>'12'];
            $strip = substr($req->tgl_lhr,4,1);
            if($strip == '-'){
                $tgl_lhr = substr($req->tgl_lhr,0,10);
            }else{
                $arr = explode(' ', $req->tgl_lhr);
                if(sizeOf($arr) > 1){
                  $tgl_lhr = $arr[3].'-'.$bulan[$arr[1]].'-'.$arr[2];
                }
            }

            DB::table('ta_ppdb_pendaftar')->where('id',$id)->update([
                'alamat'  => $req->alamat,
                'nm_bpk'  => $req->nm_bpk,
                'nik_bpk'  => $req->nik_bpk,
                'hp_bpk'  => $req->hp_bpk,
                'alamat_bpk'  => $req->alamat_bpk,
                'tempat_lhr'  => $req->tempat_lhr,
                'tgl_lhr'  => $tgl_lhr,
                'nm_ibu'  => $req->nm_ibu,
                'nik_ibu'  => $req->nik_ibu,
                'asal_sek'  => $req->asal_sek,
                'asal_alamat'  => $req->asal_alamat,
                'status_terima'  => $req->status_terima,
                'ket_status'  => $req->ket_status,
                'jalur'  => $req->jalur,
                'updated_by'  => Auth::id().' - '.Auth::user()->name,
            ]);

            if(isset($req->files)){
                $jml  = $req->jmlFile;
                $pathUrl  = Config::getPathUploadAWS();
                for($i=0; $i < $jml; $i++){
                  $judul  = 'Judul';
                  if(isset($req->title)){
                      if(isset($req->title[$i]))  $judul  = $req->title[$i];
                  }

                  foreach ($req->files as $file) {
                      $mime  = File::mimeType($file[$i]);
                      $fileName = date('Ymdhis').rand(20,100).'.'.$file[$i]->getClientOriginalExtension();
                      $resume = $file[$i];
                      $filePath = Storage::disk('s3')->putFileAs(
                          $pathUrl,
                          $resume,
                          $fileName,
                          [ 'visibility' => 'public' ]
                      );
                      DB::table('ta_ppdb_pendaftar_files')->insert([
                        'id_pendaftar'  => $register->id,
                        'judul'    => $judul,
                        'path'    => $filePath,
                        'mime'    => $mime,
                        'created_by'  => Auth::id().' - '.Auth::user()->name,
                        'updated_by'  => Auth::id().' - '.Auth::user()->name,
                      ]);
                      sleep(1);
                  }
                }
            }
        }else{
            $error  = 1; $pesan = 'Data Tidak Ditemukan';
        }

        return ['data'=>$req->all() , 'error'=>$error, 'pesan'=>$pesan ];
    }

    static function HistoryUpdateRegisterPPDB($req){
        $id  = Crypt::decrypt($req->daftar);
        $no  = 0;
        $data  = DB::table('ta_ppdb_pendaftar_history')->where('id_ppdb_pendaftar',$id)->orderBy('id','DESC')->get();
        foreach($data as $dat){
            $no++;
            $datas[]  = [
              'id'  => $dat->id,
              'nama'  => $dat->nama,
              'nik' => $dat->nik,
              'alamat'  => $dat->alamat,
              'nm_bpk'  => $dat->nm_bpk,
              'nik_bpk'  => $dat->nik_bpk,
              'nm_ibu'  => $dat->nm_ibu,
              'nik_ibu'  => $dat->nik_ibu,
              'alamat_bpk'  => $dat->alamat_bpk,
              'asal_sek'  => $dat->asal_sek,
              'asal_alamat'  => $dat->asal_alamat,
              'urut'  => $no
            ];
        }
        if(!sizeOf($data)) $datas  = [];
        return ['data'=>$datas ];
    }

    static function GetFileSiswaRegister($req){
        if(isset($req->daftar)){
            $id     = Crypt::decrypt($req->daftar);
            $files  = DB::table('ta_ppdb_pendaftar_files')->where('id_pendaftar',$id)->where('status',1)->get();
            foreach($files as $dat){
                $data[]  = [
                  'id'  => $dat->id,
                  'judul' => $dat->judul,
                  'path' => Config::getBucketAWS().$dat->path,
                ];
            }
            if(!sizeOf($files)) $data  = [];
        }else{
            $data  = [];
        }
        return ['data'=>$data, 'req'=>$req->all() ];
    }

    static function HapusFileSiswaRegister($req){
        $file  = DB::table('ta_ppdb_pendaftar_files')->where('id',$req->id)->first();
        if($file){
            $error = 0; $pesan = 'Data Berhasil Dihapus';
            DB::table('ta_ppdb_pendaftar_files')->where('id',$req->id)->update([
              'status'  => 0
            ]);
        }else{
            $error = 1; $pesan = 'Data Gagal Dihapus, Data Tidak Ditemukan';
        }
        return ['data'=>$req->all(), 'error'=>$error, 'pesan'=>$pesan];
    }

    static function RegisterPPDBByNisn($req){
        $tahun  = DB::table('ref_tahun')->where('id',$req->thn_ajar)->value('tahun');
        $id_ppdb_sek = DB::table('ta_ppdb_sek')->where('id_inst',1)->where('id_sek',$req->id_sek)->where('tahun',$tahun)->value('id');
        $ma  = DB::table('dapodik_siswa_akhir')->orWhere('nisn',$req->nisn)->orWhere('nik',$req->nik)->first();
        if($ma){
            $cek  = DB::table('ta_ppdb_pendaftar')->where('ta',$tahun)->where('nisn',$ma->nisn)->count();
            if($cek >= 3){
                return [
                  'error'=>1, 'pesan'=>'Maaf '.$ma->nama.', Maksimal Pendaftaran hanya 3 kali', 'data'=>'',
                  'req'=>$req->all(),
                  'pesan_error' => [],
                 ];
            }else{
                $cek  = DB::table('ta_ppdb_pendaftar')->where('id_sek',$req->id_sek)->where('ta',$tahun)->where('nisn',$ma->nisn)->count();
                if($cek){
                    return [
                      'error'=>1, 'pesan'=>'Maaf, '.$ma->nama.' Sudah Mendaftar di Sekolah ini TA. '.$tahun, 'data'=>'',
                      'req'=>$req->all(),
                      'pesan_error' => [],
                     ];
                }
            }

            if($ma->jenis_kelamin == 'L') $jk = 1;
            else $jk = 2;
            $no_peserta = DB::table('ta_ppdb_pendaftar')->select('no_peserta')->orderBy('no_peserta','DESC')->value('no_peserta');
            $no_peserta++;
            $id = DB::table('ta_ppdb_pendaftar')->insertGetId([
                'id_inst' => 1,
                'id_ppdb_sek' => $id_ppdb_sek,
                'id_sek'  => $req->id_sek,
                'no_peserta'  => $no_peserta,
                'no_usbn' => 0,
                'nama'  => $ma->nama,
                'nik' => $ma->nik,
                'nisn' => $ma->nisn,
                'jk'  => $jk,
                'tempat_lhr'  => $ma->tempat_lahir,
                'tgl_lhr' => $ma->tanggal_lahir,
                'nik' => $ma->nik,
                'alamat' => $ma->alamat_jalan.' RT/RW: '.$ma->rt.'/'.$ma->rw,
                'nm_bpk' => $ma->nama_ayah,
                'nik_bpk' => 0,
                'hp_bpk' => $req->hp_bpk,
                'alamat_bpk' => $ma->alamat_jalan.' RT/RW: '.$ma->rt.'/'.$ma->rw,
                'nm_ibu' => $ma->nama_ibu_kandung,
                'nik_ibu' => 0,
                'asal_sek' => $req->nm_sek,
                'asal_alamat' => $req->alamat_sek,
                'thn_lulus' => date('Y'),
                'jalur' => $req->id_jalur,
            ]);

            if($id){
                if(isset($req->file_ak)){
                    self::UploadFile($req->file_ak,$id,'Akte Kelahiran');
                    sleep(1);
                }
                if(isset($req->file_kk)){
                    self::UploadFile($req->file_kk,$id,'Kartu Keluarga');
                    sleep(1);
                }
                if(isset($req->file_skl)){
                    self::UploadFile($req->file_skl,$id,'Surat Keterangan Lulus');
                    sleep(1);
                }
                if(isset($req->file_ket_mis)){
                    self::UploadFile($req->file_ket_mis,$id,'Surat Keterangan Miskin dari Desa/Kelurahan');
                    sleep(1);
                }
                if(isset($req->file_pindah)){
                    self::UploadFile($req->file_pindah,$id,'Surat Keterangan Pindah');
                    sleep(1);
                }
                if(isset($req->file_raport)){
                    self::UploadFile($req->file_raport,$id,'Rapor');
                    sleep(1);
                }
            }

            $dat  = DB::table('ta_ppdb_pendaftar')->where('id',$id)->first();
            if($dat){
                $sek  = DB::table('ta_sekolah')->where('id',$dat->id_sek)->first();
                $data  = [
                  'no_peserta'  => $dat->no_peserta,
                  'nama'  => $dat->nama,
                  'tempat_lhr'  => $dat->tempat_lhr,
                  'tgl_lhr' => $dat->tgl_lhr,
                  'sek_tujuan'  => $sek->nama,
                  'id_'  => Crypt::encrypt($dat->id)
                ];
                return ['error'=>0, 'pesan'=>'Sukses Register Peserta', 'data'=>$data];
            }else{
                return ['error'=>1, 'pesan'=>'Gagal Register Peserta', 'data'=>''];
            }

        }
        return [
          'error' => 1,
          'pesan' => 'Register Gagal',
          'ma'  => $ma,
          'no_peserta'  => $no_peserta,
          'req' => $req->all()
        ];
    }

    static function UploadFile($file,$id,$judul){
        $pathUrl  = Config::getPathUploadAWS();
        $mime  = File::mimeType($file);
        $fileName = date('Ymdhis').rand(20,100).'.'.$file->getClientOriginalExtension();
        $resume = $file;
        $filePath = Storage::disk('s3')->putFileAs(
            $pathUrl,
            $resume,
            $fileName,
            [ 'visibility' => 'public' ]
        );
        DB::table('ta_ppdb_pendaftar_files')->insert([
          'id_pendaftar'  => $id,
          'judul'    => $judul,
          'path'    => $filePath,
          'mime'    => $mime,
          'created_by'  => 'Create By Register',
          'updated_by'  => 'Update By Register',
        ]);
    }

    static function OprAddPPDB($req){
        $error = 1; $pesan = 'Gagal Tambah PPDB';
        $ppdb = DB::table('ta_ppdb_sek')->where('id',$req->id_ppdb_sek)->first();
        $ma  = DB::table('dapodik_siswa_akhir')->where('id',$req->id)->first();
        if($ma){
            if($ma->jenis_kelamin == 'L') $jk = 1;
            else $jk = 2;
            $no_peserta = DB::table('ta_ppdb_pendaftar')->select('no_peserta')->orderBy('no_peserta','DESC')->value('no_peserta');
            $no_peserta++;
            $id = DB::table('ta_ppdb_pendaftar')->insertGetId([
                'id_inst' => 1,
                'id_ppdb_sek' => $ppdb->id,
                'id_sek'  => $ppdb->id_sek,
                'no_peserta'  => $no_peserta,
                'no_usbn' => 0,
                'nama'  => $ma->nama,
                'nik' => $ma->nik,
                'nisn' => $ma->nisn,
                'jk'  => $jk,
                'tempat_lhr'  => $ma->tempat_lahir,
                'tgl_lhr' => $ma->tanggal_lahir,
                'nik' => $ma->nik,
                'alamat' => $ma->alamat_jalan.' RT/RW: '.$ma->rt.'/'.$ma->rw,
                'nm_bpk' => $ma->nama_ayah,
                'nik_bpk' => 0,
                'hp_bpk' => $req->hp_bpk,
                'alamat_bpk' => $ma->alamat_jalan.' RT/RW: '.$ma->rt.'/'.$ma->rw,
                'nm_ibu' => $ma->nama_ibu_kandung,
                'nik_ibu' => 0,
                'asal_sek' => $ppdb->nm_sek,
                'asal_alamat' => $ppdb->alamat,
                'thn_lulus' => date('Y'),
                'jalur' => 1
            ]);
            $error = 0; $pesan = 'Berhasil Tambah Data '.$ma->nama;
        }

        return [
          'req' => $req->all(),
          'error' => $error,
          'pesan' => $pesan,
        ];
    }

    static function CekStatusPendaftaran($req){
        $error = true; $message = 'Cari Data Berhasil'; $no = 1;
        $data  = DB::table('ta_ppdb_pendaftar')->orWhere('nisn',$req->nisn)->orWhere('nik',$req->nisn)->get();
        foreach($data as $dat){
            $ta  = DB::table('ta_ppdb_sek')->where('id',$dat->id_ppdb_sek)->value('tahun_ajaran');
            $tujuan  = DB::table('ta_sekolah')->where('id',$dat->id_sek)->value('nama');
            $datas[] = [
              'id'  => $dat->id,
              'urut'  => $no,
              'nama'  => $dat->nama,
              'nisn'  => $dat->nisn,
              'nik'  => $dat->nik,
              'nm_bpk'  => $dat->nm_bpk,
              'nm_ibu'  => $dat->nm_ibu,
              'alamat'  => $dat->alamat,
              'ttl' => $dat->tempat_lhr.' / '.$dat->tgl_lhr,
              'status'  => $dat->status_terima,
              'keterangan'  => $dat->ket_status,
              'sek_tujuan'  => $tujuan,
              'ta'  => $ta,
            ];
            $no++;
        }

        if(!sizeOf($data)){
            $status = false; $message = 'Data Tidak Ditemukan'; $datas = [];
        }
        return response()->json([
            'status'  => $status,
            'message' => $message,
            'data'  => $datas,
        ]);
    }

    static function GetRegById($req){
        // digunakan di :
        // 1. GuestStatusDaftarRincComponent

        $is_update = 0;
        $cek  = DB::table('ta_ppdb_pendaftar')->where('id',$req->id)->first();
        if($cek){
            $data = [
              'id'  => $cek->id,
              'nama'  => $cek->nama,
              'nik' => $cek->nik,
              'nisn'  => $cek->nisn,
              'ttl' => $cek->tempat_lhr.' / '.$cek->tgl_lhr,
              'nm_ibu'  => $cek->nm_ibu
            ];
            if($cek->status_terima == 1){
                $is_update = 1;
            }
            $file = DB::table('ta_ppdb_pendaftar_files')->where('id_pendaftar',$cek->id)->get();
            foreach($file as $dat){
                $files[] = [
                    'id'  => $dat->id,
                    'judul' => $dat->judul,
                    'path' => Config::getBucketAWS().$dat->path,
                ];
            }
            if(!sizeOf($file)) $files = [];
            $error = 0; $pesan = '';

            $sek  = DB::table('ta_sekolah')->where('id',$cek->id_sek)->first();
            $jenjang = $sek->id;
        }else{
            $files = [];
            $data = ''; $error = 1; $pesan = 'Data Tidak Ditemukan';
            $jenjang = 'x';
        }

        return [
          'req' => $req->all(),
          'error' => $error,
          'pesan' => $pesan,
          'data'  => $data,
          'files' => $files,
          'jenjang' => $jenjang,
          'is_update' => $is_update,
        ];
    }

    static function UpdateRegById($req){
        // digunakan di :
        // 1. GuestStatusDaftarRincComponent
        $is_update = 0;
        $data = ''; $error = 1; $pesan = 'Data Tidak Ditemukan';
        $cek  = DB::table('ta_ppdb_pendaftar')->where('id',$req->id)->first();
        if($cek){
            $data = [
              'id'  => $cek->id,
              'nama'  => $cek->nama,
              'nik' => $cek->nik,
              'nisn'  => $cek->nisn,
              'ttl' => $cek->tempat_lhr.' / '.$cek->tgl_lhr,
              'nm_ibu'  => $cek->nm_ibu
            ];
            if($cek->status_terima == 1){
                $is_update = 1;
            }

            $id  = $req->id;
            if(isset($req->file_ak)){
                self::UploadFile($req->file_ak,$id,'Akte Kelahiran');
            }
            if(isset($req->file_kk)){
                self::UploadFile($req->file_kk,$id,'Kartu Keluarga');
            }
            if(isset($req->file_skl)){
                self::UploadFile($req->file_skl,$id,'Surat Keterangan Lulus');
            }
            if(isset($req->file_ket_mis)){
                self::UploadFile($req->file_ket_mis,$id,'Surat Keterangan Miskin dari Desa/Kelurahan');
            }
            if(isset($req->file_pindah)){
                self::UploadFile($req->file_pindah,$id,'Surat Keterangan Pindah');
            }
            if(isset($req->file_raport)){
                self::UploadFile($req->file_raport,$id,'Rapor');
            }

            $file = DB::table('ta_ppdb_pendaftar_files')->where('id_pendaftar',$cek->id)->get();
            foreach($file as $dat){
                $files[] = [
                    'id'  => $dat->id,
                    'judul' => $dat->judul,
                    'path' => Config::getBucketAWS().$dat->path,
                ];
            }
            if(!sizeOf($file)) $files = [];
            $error = 0; $pesan = 'Data Berhasil Di Update';
        }else{
            $files = [];
            $data = ''; $error = 1; $pesan = 'Data Tidak Ditemukan';
            $jenjang = 'x';
        }

        return [
          'req' => $req->all(),
          'error' => $error,
          'pesan' => $pesan,
          'data'  => $data,
          'files' => $files,
          'is_update' => $is_update
        ];
    }

    static function OprAddManualPPDB($req){
        $error = 1; $pesan = 'Gagal Tambah Data '.$req->nama;

        $ppdb  = DB::table('ta_ppdb_sek')->where('id',$req->id_ppdb_sek)->first();
        if(!$ppdb){
          return [
            'error' => $error,
            'pesan' => $pesan,
            'req' => $req->all()
          ];
        }

        $bulan  = ['Jan'=>'01','Feb'=>'02','Mar'=>'03', 'Apr'=>'04', 'May'=>'05', 'Jun'=>'06', 'Jul'=>'07', 'Aug'=>'08', 'Sep'=>'09', 'Oct'=>'10', 'Nov'=>'11', 'Dec'=>'12'];
        $strip = substr($req->tgl_lhr,4,1);
        if($strip == '-'){
            $tgl_lhr = substr($req->tgl_lhr,0,10);
        }else{
            $arr = explode(' ', $req->tgl_lhr);
            if(sizeOf($arr) > 1){
              $tgl_lhr = $arr[3].'-'.$bulan[$arr[1]].'-'.$arr[2];
            }
        }

        $no_peserta = DB::table('ta_ppdb_pendaftar')->select('no_peserta')->orderBy('no_peserta','DESC')->value('no_peserta');
        $no_peserta++;
        $id = DB::table('ta_ppdb_pendaftar')->insertGetId([
            'id_inst' => 1,
            'id_ppdb_sek' => $req->id_ppdb_sek,
            'id_sek'  => $ppdb->id_sek,
            'no_peserta'  => $no_peserta,
            'no_usbn' => 0,
            'nama'  => $req->nama,
            'nik' => $req->nik,
            'nisn' => $req->nisn,
            'jk'  => $req->jk,
            'tempat_lhr'  => $req->tempat_lhr,
            'tgl_lhr' => $tgl_lhr,
            'alamat' => $req->alamat,
            'nm_bpk' => $req->nm_bpk,
            'nik_bpk' => $req->nik_bpk,
            'hp_bpk' => $req->hp_bpk,
            'alamat_bpk' => $req->alamat_bpk,
            'nm_ibu' => $req->nm_ibu,
            'nik_ibu' => $req->nik_ibu,
            'asal_sek' => $req->asal_sek,
            'asal_alamat' => $req->asal_alamat,
            'thn_lulus' => $req->thn_lulus,
            'jalur' => $req->jalur
        ]);
        if($id){
            $error = 0; $pesan = 'Berhasil Tambah Data '.$req->nama;
        }

        return [
          'error' => $error,
          'pesan' => $pesan,
          'req' => $req->all()
        ];
    }

}
