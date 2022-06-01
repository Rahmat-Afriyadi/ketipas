<?php

namespace App\Http\Controllers\Vaksin;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use View;
use Str;
use App\Http\Controllers\Admin\HAController as HA;

class VaksinController extends Controller{

    static function FilterTenagaPendidik($req){
        $id_user  = Auth::ID(); $success  = true; $message = 'Gagal Get Data';
        $admin  = DB::table('users')->where('id',$id_user)->where('admin',1)->where('status',1)->count();
        if(!$admin){
            $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
            if($opr){
                  $sek    = DB::table('ta_sekolah')->select('id','id_kec')->where('id',$opr->id_sek);
                  $data  = $sek->get();
                  foreach($data as $dat){
                      $id_sek[] = $dat->id;
                  }
                  if(!sizeOf($data)) $id_sek[] = 0;
            }else{
                $id_sek[]  = 0;
            }
        }else{
              $sek    = DB::table('ta_sekolah')->select('id','id_kec');
              if($req->id_kec) $sek->where('id_kec',$req->id_kec);
              if($req->id_sek) $sek->where('id',$req->id_sek);
              if($req->jenjang) $sek->where('jenjang',$req->jenjang);
              $data  = $sek->get();
              foreach($data as $dat){
                  $id_sek[] = $dat->id;
              }
              if(!sizeOf($data)) $id_sek[] = 0;
        }

        $temp  = DB::table('ta_tendik')->whereIn('id_sek',$id_sek)
                ->where('sync',0)
                ->get();
        foreach($temp as $dtemp){
            $cek  = DB::table('ta_vaksin_guru');
            if($dtemp->nik) $cek->where('nik',$dtemp->nik);
            $dcek  = $cek->first();

            if(strlen($dtemp->tgl_lhr) == 10){
                $awal  = date_create($dtemp->tgl_lhr);
                $akhir = date_create(); // waktu sekarang
                $diff  = date_diff( $awal, $akhir );
                $tgl_lhr = $dtemp->tgl_lhr;
            }else{
                $awal  = date_create(date('Y-m-d'));
                $akhir = date_create(); // waktu sekarang
                $diff  = date_diff( $awal, $akhir );
                $tgl_lhr = date('Y-m-d');
            }

            if($dcek){
                DB::table('ta_vaksin_guru')->where('id',$dcek->id)->update([
                    'jk'  => $dtemp->jk,
                    'tmp_lhr'  => $dtemp->tmp_lhr,
                    'tgl_lhr'  => $tgl_lhr,
                    'agama'  => $dtemp->agama,
                    'thn' => $diff->y,
                    'bln' => $diff->m,
                    'hri' => $diff->d,
                ]);
                DB::table('ta_tendik')->where('id',$dtemp->id)->update(['sync'=>1]);
            }else{
                DB::table('ta_vaksin_guru')->insert([
                    'id_sek'  => $dtemp->id_sek,
                    'nama'  => $dtemp->nama,
                    'nik'  => $dtemp->nik,
                    'jk'  => $dtemp->jk,
                    'tmp_lhr'  => $dtemp->tmp_lhr,
                    'tgl_lhr'  => $tgl_lhr,
                    'agama'  => $dtemp->agama,
                    'thn' => $diff->y,
                    'bln' => $diff->m,
                    'hri' => $diff->d,
                ]);
                DB::table('ta_tendik')->where('id',$dtemp->id)->update(['sync'=>1]);
            }
        }

        $query   = DB::table('ta_vaksin_guru')->whereIn('id_sek',$id_sek);
        if($req->laporan) $query->where('tdk_vaksin',0)->where('blm_vaksin',0);
        $datas   = $query->orderBy('nama')->get();

        return response()->json([
            'success'  => $success,
            'message' => $message,
            'data'  => $datas,
        ]);
    }

    static function FilterTenagaKependidikan($req){
        $id_user  = Auth::ID();
        $admin  = DB::table('users')->where('id',$id_user)->where('admin',1)->where('status',1)->count();
        if(!$admin){
            $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
            if($opr){
                  $sek    = DB::table('ta_sekolah')->select('id','id_kec')->where('id',$opr->id_sek);
                  $data  = $sek->get();
                  foreach($data as $dat){
                      $id_sek[] = $dat->id;
                  }
                  if(!sizeOf($data)) $id_sek[] = 0;
            }else{
                $id_sek[]  = 0;
            }
        }else{
              $sek    = DB::table('ta_sekolah')->select('id','id_kec');
              if($req->id_kec) $sek->where('id_kec',$req->id_kec);
              if($req->id_sek) $sek->where('id',$req->id_sek);
              if($req->jenjang) $sek->where('jenjang',$req->jenjang);
              $data  = $sek->get();
              foreach($data as $dat){
                  $id_sek[] = $dat->id;
              }
              if(!sizeOf($data)) $id_sek[] = 0;
        }

        $temp  = DB::table('ta_non_tendik')->whereIn('id_sek',$id_sek)
                ->where('sync',0)
                ->get();
        foreach($temp as $dtemp){
            $cek  = DB::table('ta_vaksin_tendik');
            if($dtemp->nik) $cek->where('nik',$dtemp->nik);
            $dcek  = $cek->first();

            if(strlen($dtemp->tgl_lhr) == 10){
                $awal  = date_create($dtemp->tgl_lhr);
                $akhir = date_create(); // waktu sekarang
                $diff  = date_diff( $awal, $akhir );
                $tgl_lhr = $dtemp->tgl_lhr;
            }else{
                $awal  = date_create(date('Y-m-d'));
                $akhir = date_create(); // waktu sekarang
                $diff  = date_diff( $awal, $akhir );
                $tgl_lhr = date('Y-m-d');
            }

            if($dcek){
                DB::table('ta_vaksin_tendik')->where('id',$dcek->id)->update([
                    'jk'  => $dtemp->jk,
                    'tmp_lhr'  => $dtemp->tmp_lhr,
                    'tgl_lhr'  => $tgl_lhr,
                    'agama'  => $dtemp->agama,
                    'thn' => $diff->y,
                    'bln' => $diff->m,
                    'hri' => $diff->d,
                ]);
                DB::table('ta_non_tendik')->where('id',$dtemp->id)->update(['sync'=>1]);
            }else{
                DB::table('ta_vaksin_tendik')->insert([
                    'id_sek'  => $dtemp->id_sek,
                    'nama'  => $dtemp->nama,
                    'nik'  => $dtemp->nik,
                    'jk'  => $dtemp->jk,
                    'tmp_lhr'  => $dtemp->tmp_lhr,
                    'tgl_lhr'  => $tgl_lhr,
                    'agama'  => $dtemp->agama,
                    'thn' => $diff->y,
                    'bln' => $diff->m,
                    'hri' => $diff->d,
                ]);
                DB::table('ta_non_tendik')->where('id',$dtemp->id)->update(['sync'=>1]);
            }
        }


        $query   = DB::table('ta_vaksin_tendik')->whereIn('id_sek',$id_sek);
        if($req->laporan) $query->where('tdk_vaksin',0)->where('blm_vaksin',0);
        $datas   = $query->orderBy('nama')->get();

        $success = true; $message = 'Sukses';
        return response()->json([
            'success'  => $success,
            'message' => $message,
            'data'  => $datas,
        ]);
    }

    static function FilterPesertaDidik($url,$req){
        $id_user  = Auth::ID();
        $admin  = DB::table('users')->where('id',$id_user)->where('admin',1)->where('status',1)->count();
        if(!$admin){
            $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
            if($opr){
                  $sek    = DB::table('ta_sekolah')->select('id','id_kec')->where('id',$opr->id_sek);
                  $data  = $sek->get();
                  foreach($data as $dat){
                      $id_sek[] = $dat->id;
                  }
                  if(!sizeOf($data)) $id_sek[] = 0;
            }else{
                $id_sek[]  = 0;
            }
        }else{
              $sek    = DB::table('ta_sekolah')->select('id','id_kec');
              if($req->id_kec) $sek->where('id_kec',$req->id_kec);
              if($req->id_sek) $sek->where('id',$req->id_sek);
              if($req->jenjang) $sek->where('jenjang',$req->jenjang);
              $data  = $sek->get();
              foreach($data as $dat){
                  $id_sek[] = $dat->id;
              }
              if(!sizeOf($data)) $id_sek[] = 0;
        }

        $temp  = DB::table('ta_siswa')->whereIn('id_sek',$id_sek)
                ->where('sync',0)
                ->get();
        foreach($temp as $dtemp){
            $cek  = DB::table('ta_vaksin');
            if($dtemp->nik) $cek->where('nik',$dtemp->nik);
            elseif($dtemp->nisn) $cek->where('nisn',$dtemp->nisn);
            elseif($dtemp->nipd) $cek->where('nipd',$dtemp->nipd);
            $dcek  = $cek->first();

            $awal  = date_create($dtemp->tgl_lhr);
            $akhir = date_create(); // waktu sekarang
            $diff  = date_diff( $awal, $akhir );
            $wajib = 0;
            if($diff->y == 6 && $diff->m > 8){
                $wajib = 1;
            }elseif($diff->y >= 12){
                $wajib = 1;
            }
            $wajib = 1;

            if($dcek){
                DB::table('ta_vaksin')->where('id',$dcek->id)->update([
                    'nama'  => $dtemp->nama,
                    'nipd'  => $dtemp->nipd,
                    'jk'  => $dtemp->jk,
                    'nisn'  => $dtemp->nisn,
                    'tmp_lhr'  => $dtemp->tmp_lhr,
                    'tgl_lhr'  => $dtemp->tgl_lhr,
                    'nik'  => $dtemp->nik,
                    'agama'  => $dtemp->agama,
                    'thn' => $diff->y,
                    'bln' => $diff->m,
                    'hri' => $diff->d,
                    'is_wajib'  => $wajib,
                ]);
                DB::table('ta_siswa')->where('id',$dtemp->id)->update(['sync'=>1]);
            }else{
                DB::table('ta_vaksin')->insert([
                    'id_sek'  => $dtemp->id_sek,
                    'nama'  => $dtemp->nama,
                    'nipd'  => $dtemp->nipd,
                    'jk'  => $dtemp->jk,
                    'nisn'  => $dtemp->nisn,
                    'tmp_lhr'  => $dtemp->tmp_lhr,
                    'tgl_lhr'  => $dtemp->tgl_lhr,
                    'nik'  => $dtemp->nik,
                    'agama'  => $dtemp->agama,
                    'thn' => $diff->y,
                    'bln' => $diff->m,
                    'hri' => $diff->d,
                    'is_wajib'  => $wajib,
                ]);
                DB::table('ta_siswa')->where('id',$dtemp->id)->update(['sync'=>1]);
            }
        }

        $query   = DB::table('ta_vaksin')->whereIn('id_sek',$id_sek)->where('is_wajib',1);
        if($req->laporan) $query->where('tdk_vaksin',0);
        $datas   = $query->orderBy('nama')->get();
        return [
          'req' => $req->all(),
          'data'  => $datas,
        ];
    }

    static function UpdateVaksinTenagaPendidik($url,$req){
        $error = 1;
        $pesan = 'Data Gagal Di Update';
        if($req->ket == 1){
            if($req->status) $status = 0;
            else $status = 1;
            DB::table('ta_vaksin_guru')->where('id',$req->id)->update([
                'vaksin1' => $status,
            ]);
            $error = 0;
            $pesan = 'Data Tenaga Pendidik Berhasil Di Update';
        }elseif($req->ket == 2){
            if($req->status) $status = 0;
            else $status = 1;
            DB::table('ta_vaksin_guru')->where('id',$req->id)->update([
                'vaksin2' => $status,
            ]);
            $error = 0;
            $pesan = 'Data Tenaga Pendidik Berhasil Di Update';
        }elseif($req->ket == 5){
            if($req->status) $status = 0;
            else $status = 1;
            DB::table('ta_vaksin_guru')->where('id',$req->id)->update([
                'vaksin3' => $status,
            ]);
            $error = 0;
            $pesan = 'Data Tenaga Pendidik Berhasil Di Update';
        }elseif($req->ket == 3){
            if($req->status) $status = 0;
            else $status = 1;
            DB::table('ta_vaksin_guru')->where('id',$req->id)->update([
                'blm_vaksin' => $status,
            ]);
            $error = 0;
            $pesan = 'Data Tenaga Pendidik Berhasil Di Update';
        }elseif($req->ket == 4){
            if($req->status) $status = 0;
            else $status = 1;
            DB::table('ta_vaksin_guru')->where('id',$req->id)->update([
                'tdk_vaksin' => $status,
            ]);
            $error = 0;
            $pesan = 'Data Tenaga Pendidik Berhasil Di Update';
        }

        return [
          'req' => $req->all(),
          'error' => $error,
          'pesan' => $pesan,
        ];
    }

    static function UpdateVaksinTendik($url,$req){
        $error = 1;
        $pesan = 'Data Gagal Di Update';
        if($req->ket == 1){
            if($req->status) $status = 0;
            else $status = 1;
            DB::table('ta_vaksin_tendik')->where('id',$req->id)->update([
                'vaksin1' => $status,
            ]);
            $error = 0;
            $pesan = 'Data Tenaga Kependidikan Berhasil Di Update';
        }elseif($req->ket == 2){
            if($req->status) $status = 0;
            else $status = 1;
            DB::table('ta_vaksin_tendik')->where('id',$req->id)->update([
                'vaksin2' => $status,
            ]);
            $error = 0;
            $pesan = 'Data Tenaga Kependidikan Berhasil Di Update';
        }elseif($req->ket == 5){
            if($req->status) $status = 0;
            else $status = 1;
            DB::table('ta_vaksin_tendik')->where('id',$req->id)->update([
                'vaksin3' => $status,
            ]);
            $error = 0;
            $pesan = 'Data Tenaga Kependidikan Berhasil Di Update';
        }elseif($req->ket == 3){
            if($req->status) $status = 0;
            else $status = 1;
            DB::table('ta_vaksin_tendik')->where('id',$req->id)->update([
                'blm_vaksin' => $status,
            ]);
            $error = 0;
            $pesan = 'Data Tenaga Kependidikan Berhasil Di Update';
        }elseif($req->ket == 4){
            if($req->status) $status = 0;
            else $status = 1;
            DB::table('ta_vaksin_tendik')->where('id',$req->id)->update([
                'tdk_vaksin' => $status,
            ]);
            $error = 0;
            $pesan = 'Data Tenaga Kependidikan Berhasil Di Update';
        }

        return [
          'req' => $req->all(),
          'error' => $error,
          'pesan' => $pesan,
        ];
    }

    static function UpdateVaksinPD($url,$req){
        $error = 1;
        $pesan = 'Data Gagal Di Update';
        if($req->ket == 1){
            if($req->status) $status = 0;
            else $status = 1;
            DB::table('ta_vaksin')->where('id',$req->id)->update([
                'vaksin1' => $status,
            ]);
            $error = 0;
            $pesan = 'Data Berhasil Di Update';
        }elseif($req->ket == 2){
            if($req->status) $status = 0;
            else $status = 1;
            DB::table('ta_vaksin')->where('id',$req->id)->update([
                'vaksin2' => $status,
            ]);
            $error = 0;
            $pesan = 'Data Berhasil Di Update';
        }elseif($req->ket == 5){
            if($req->status) $status = 0;
            else $status = 1;
            DB::table('ta_vaksin')->where('id',$req->id)->update([
                'vaksin3' => $status,
            ]);
            $error = 0;
            $pesan = 'Data Berhasil Di Update';
        }elseif($req->ket == 3){
            if($req->status) $status = 0;
            else $status = 1;
            DB::table('ta_vaksin')->where('id',$req->id)->update([
                'blm_vaksin' => $status,
            ]);
            $error = 0;
            $pesan = 'Data Berhasil Di Update';
        }elseif($req->ket == 4){
            if($req->status) $status = 0;
            else $status = 1;
            DB::table('ta_vaksin')->where('id',$req->id)->update([
                'tdk_vaksin' => $status,
            ]);
            $error = 0;
            $pesan = 'Data Berhasil Di Update';
        }

        return [
          'req' => $req->all(),
          'error' => $error,
          'pesan' => $pesan,
        ];
    }

    static function PrintPD($url){
        if(\Request::isMethod('POST')){

            $id_user  = Auth::ID();
            $admin  = DB::table('users')->where('id',$id_user)->where('admin',1)->where('status',1)->count();
            if(!$admin){
                $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
                if($opr){
                      $sek    = DB::table('ta_sekolah')->select('id','id_kec')->where('id',$opr->id_sek);
                      $data  = $sek->get();
                      foreach($data as $dat){
                          $id_sek[] = $dat->id;
                      }
                      if(!sizeOf($data)) $id_sek[] = 0;
                }else{
                    $id_sek[]  = 0;
                }
            }else{
                  $exp   = explode('-',$url);
                  $id_kec = $exp[0];
                  // $id_sek  = $exp[1];
                  $jenjang = $exp[2];

                  $sek    = DB::table('ta_sekolah')->select('id','id_kec');
                  if($id_kec) $sek->where('id_kec',$id_kec);
                  if($exp[1]) $sek->where('id',$exp[1]);
                  if($jenjang) $sek->where('jenjang',$jenjang);
                  $data  = $sek->get();
                  foreach($data as $dat){
                      $id_sek[] = $dat->id;
                  }
                  if(!sizeOf($data)) $id_sek[] = 0;
            }

            $dat  = DB::table('ta_vaksin')->whereIn('id_sek',$id_sek)->where('tdk_vaksin',0)
                    // ->where('blm_vaksin',0)
                    ->where('is_wajib',1)->orderBy('nama')->get();

            return [
              'data' => $dat,
              'judul' => [
                'REKAPITULASI DATA VAKSIN',
              ],
              'otoritas'  => true,
              'dat'  => $id_kec.' - '.$exp[1].' - '.$jenjang
            ];

        }

        $data['url']  = $url;
        $data['title']  = 'Data Vaksin Peserta Didik';
        $data['desc']  = 'Data Vaksin Peserta Didik';
        return view('vaksin.pdf.pdf_pd',$data);
    }

    static function PdfGuru($url){
        if(\Request::isMethod('POST')){

            $id_user  = Auth::ID();
            $admin  = DB::table('users')->where('id',$id_user)->where('admin',1)->where('status',1)->count();
            if(!$admin){
                $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
                if($opr){
                      $sek    = DB::table('ta_sekolah')->select('id','id_kec')->where('id',$opr->id_sek);
                      $data  = $sek->get();
                      foreach($data as $dat){
                          $id_sek[] = $dat->id;
                      }
                      if(!sizeOf($data)) $id_sek[] = 0;
                }else{
                    $id_sek[]  = 0;
                }
            }else{
                  $exp   = explode('-',$url);
                  $id_kec = $exp[0];
                  // $id_sek  = $exp[1];
                  $jenjang = $exp[2];

                  $sek    = DB::table('ta_sekolah')->select('id','id_kec');
                  if($id_kec) $sek->where('id_kec',$id_kec);
                  if($exp[1]) $sek->where('id',$exp[1]);
                  if($jenjang) $sek->where('jenjang',$jenjang);
                  $data  = $sek->get();
                  foreach($data as $dat){
                      $id_sek[] = $dat->id;
                  }
                  if(!sizeOf($data)) $id_sek[] = 0;
            }

            $dat  = DB::table('ta_vaksin_guru')->whereIn('id_sek',$id_sek)->where('tdk_vaksin',0)
                    ->where('blm_vaksin',0)->orderBy('nama')->get();

            return [
              'data' => $dat,
              'judul' => [
                'REKAPITULASI DATA VAKSIN TENAGA PENDIDIK',
              ],
              'otoritas'  => true,
              'dat'  => $id_kec.' - '.$exp[1].' - '.$jenjang,
              'id_sek'  => $id_sek
            ];

        }

        $data['url']  = $url;
        $data['title']  = 'Data Vaksin Tenaga Pendidik';
        $data['desc']  = 'Data Vaksin Tenaga Pendidik';
        return view('vaksin.pdf.pdf_guru',$data);

    }

    static function PdfTendik($url){
        if(\Request::isMethod('POST')){

            $id_user  = Auth::ID();
            $admin  = DB::table('users')->where('id',$id_user)->where('admin',1)->where('status',1)->count();
            if(!$admin){
                $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
                if($opr){
                      $sek    = DB::table('ta_sekolah')->select('id','id_kec')->where('id',$opr->id_sek);
                      $data  = $sek->get();
                      foreach($data as $dat){
                          $id_sek[] = $dat->id;
                      }
                      if(!sizeOf($data)) $id_sek[] = 0;
                }else{
                    $id_sek[]  = 0;
                }
            }else{
                  $exp   = explode('-',$url);
                  $id_kec = $exp[0];
                  // $id_sek  = $exp[1];
                  $jenjang = $exp[2];

                  $sek    = DB::table('ta_sekolah')->select('id','id_kec');
                  if($id_kec) $sek->where('id_kec',$id_kec);
                  if($exp[1]) $sek->where('id',$exp[1]);
                  if($jenjang) $sek->where('jenjang',$jenjang);
                  $data  = $sek->get();
                  foreach($data as $dat){
                      $id_sek[] = $dat->id;
                  }
                  if(!sizeOf($data)) $id_sek[] = 0;
            }

            $dat  = DB::table('ta_vaksin_tendik')->whereIn('id_sek',$id_sek)->where('tdk_vaksin',0)
                    ->where('blm_vaksin',0)->orderBy('nama')->get();

            return [
              'data' => $dat,
              'judul' => [
                'REKAPITULASI DATA VAKSIN TENAGA PENDIDIK',
              ],
              'otoritas'  => true,
              'dat'  => $id_kec.' - '.$exp[1].' - '.$jenjang
            ];

        }

        $data['url']  = $url;
        $data['title']  = 'Data Vaksin Tenaga Pendidik';
        $data['desc']  = 'Data Vaksin Tenaga Pendidik';
        return view('vaksin.pdf.pdf_tendik',$data);

    }

    static function ExelPD($url,$req){


        $data['url']  = $url;
        $data['title']  = 'Data Vaksin Peserta Didik';
        $data['desc']  = 'Data Vaksin Peserta Didik';
        return view('vaksin.pdf.exel_pd',$data);
        return [
          'url' => $url,
          'req' => $req->all(),
        ];
    }

}
