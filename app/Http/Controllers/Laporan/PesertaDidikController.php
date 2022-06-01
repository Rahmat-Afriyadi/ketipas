<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use View;
use Str;
use App\Exports\UsersExport;
use App\Imports\PDImport;
use Maatwebsite\Excel\Facades\Excel;
use Session;

use App\Http\Controllers\Admin\HAController as HA;

class PesertaDidikController extends Controller{

    static function FilterPesertaDidik($url,$req){
        $id_user  = Auth::ID(); $success = false; $message = 'Gagal Get Data';
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
        if($req->umur == 1){
            $query->whereIn('thn',[6,7,8,9,10,11]);
        }elseif($req->umur == 2) $query->where('thn','>=',12);
        $datas   = $query->orderBy('nama')->get();

        $success = true;
        return response()->json([
            'success'  => $success,
            'message' => $message,
            'data'  => $datas
        ]);

    }

    static function indexPrint($url){
        if(\Request::isMethod('POST')){
            $id_user  = Auth::ID();
            $exp   = explode('-',$url);

            $judul[] = 'REKAPITULASI DATA VAKSIN';
            $judul[] = 'PESERTA DIDIK';

            $admin  = DB::table('users')->where('id',$id_user)->where('admin',1)->where('status',1)->count();
            if(!$admin){
                $opr  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
                if($opr){
                      $sek    = DB::table('ta_sekolah')->select('id','id_kec')->where('id',$opr->id_sek);
                      $data  = $sek->get();
                      foreach($data as $dat){
                          $id_sek[] = $dat->id;
                          $nama  = $dat->nama;
                      }
                      if(!sizeOf($data)) $id_sek[] = 0;
                      else $judul[] = $nama;
                }else{
                    $id_sek[]  = 0;
                }
            }else{
                  $sek    = DB::table('ta_sekolah')->select('id','id_kec');
                  if($exp[0]) $sek->where('id_kec',$exp[0]);
                  if($exp[1]) $sek->where('id',$exp[1]);
                  if($exp[3]){
                      $sek->where('jenjang',$exp[3]);
                      $judul[] = 'JENJANG '.$exp[3];
                  }
                  $data  = $sek->get();
                  foreach($data as $dat){
                      $id_sek[] = $dat->id;
                  }
                  if(!sizeOf($data)) $id_sek[] = 0;
                  if($exp[1]){
                      $nama    = DB::table('ta_sekolah')->where('id',$exp[1])->value('nama');
                  }else{
                      $nama    = DB::table('ref_kecamatan')->where('id',$exp[0])->value('uraian');
                  }
                  $judul[] = $nama;
            }

            if($exp[2] == 1){
                $judul[] = 'DATA PESERTA DIDIK USIA 6 SAMPAI 11 TAHUN';
            }elseif($exp[2] == 2){
                $judul[] = 'DATA PESERTA DIDIK USIA DIATAS 11 TAHUN';
            }else{
                $judul[] = 'DATA PESERTA DIDIK SEMUA USIA';
            }

            $query   = DB::table('ta_vaksin')->whereIn('id_sek',$id_sek)->where('is_wajib',1);
            if($exp[2] == 1){
                $query->whereIn('thn',[6,7,8,9,10,11]);
            }elseif($exp[2] == 2) $query->where('thn','>=',12);
            $datas   = $query->orderBy('nama')->get();

            return [
              'otoritas' => true,
              'data'  => $datas,
              'judul' => $judul,
            ];
        }else{
            $data['url']  = $url;
            $data['title']  = 'Laporan Vaksin Peserta Didik';
            $data['desc']  = 'Laporan Vaksin Peserta Didik';
            return view('laporan.pdf.vaksin_pd',$data);
        }
    }

    static function FilterDataDua($req){
        $urut = 1;
        $otoritas  = HA::GetOtoritas(Auth::id(),4);
        if($otoritas['lihat'] == 0){
            return [
              'req' => $req->all(),
              'data'  => [],
              'otoritas'  => $otoritas,
            ];
        }
        if($req->id_mapel){
            $query  = DB::table('ta_kelulusan')->where('tahun',$req->ta);
            if($req->id_sek){
                $query->where('id_sek',$req->id_sek);
            }else{
                $ids[]  = 0;
                if($req->id_kec){
                    $sek  = DB::table('ta_sekolah')->select('id')->where('id_kec',$req->id_kec)->get();
                    foreach($sek as $dsek){
                        $ids[] = $dsek->id;
                    }
                }else{
                    $sek  = DB::table('ta_sekolah')->select('id')->get();
                    foreach($sek as $dsek){
                        $ids[] = $dsek->id;
                    }
                }
                if($req->jenjang != "0"){
                    $sek  = DB::table('ta_sekolah')->select('id')->whereIn('id',$ids)->where('jenjang',$req->jenjang)->get();
                    foreach($sek as $dsek){
                        $ids2[] = $dsek->id;
                    }
                    if(!sizeOf($sek)) $ids2[] = 0;
                    $query->whereIn('id_sek',$ids2);
                }else{
                    $query->whereIn('id_sek',$ids);
                }
            }
            $data  = $query->orderBy('nama')->get();
            foreach($data as $dat){
                $nm_sek  = DB::table('ta_sekolah')->where('id',$dat->id_sek)->value('nama');
                $nilai   = DB::table('ta_kelulusan_nilai')->where('id_mapel',$req->id_mapel)->where('tahun',$dat->tahun)->where('nisn',$dat->nisn)->value('nilai');
                $datas[] = [
                  'urut'  => $urut,
                  'nisn'  => $dat->nisn,
                  'nama'  => $dat->nama,
                  'nm_sek'  => $nm_sek,
                  'nilai' => $nilai,
                ];
                $urut++;
            }
        }
        elseif($req->id_sek == 0){
            $query  = DB::table('ta_sekolah');
            if($req->id_kec) $query->where('id_kec',$req->id_kec);
            if($req->jenjang != '0') $query->where('jenjang',$req->jenjang);
            $data  = $query->get();
            foreach($data as $dat){
                $total  = DB::table('ta_kelulusan')->where('id_sek',$dat->id)->where('tahun',$req->ta)->count();
                $lulus  = DB::table('ta_kelulusan')->where('id_sek',$dat->id)->where('tahun',$req->ta)->where('is_lulus',1)->count();
                $tidak  = DB::table('ta_kelulusan')->where('id_sek',$dat->id)->where('tahun',$req->ta)->where('is_lulus',2)->count();
                $datas[] = [
                  'nama'  => $dat->nama,
                  'alamat'  => $dat->nama,
                  'urut'  => $urut,
                  'lulus'  => $lulus,
                  'tidak'  => $tidak,
                  'akhir'  => $total,
                ];
                $urut++;
            }
        }elseif($req->id_sek != 0){
            return self::RekapPerSekolah($req);
            $data = [];
        }else{
            $data = [];
        }

        if(!sizeOf($data)) $datas = [];
        $success = false; $message = 'Gagal Get Data';
        return response()->json([
            'success'  => $success,
            'message' => $message,
            'req' => $req->all(),
            'data'  => $datas,
            'otoritas'  => $otoritas,
        ]);
    }

    static function RekapPerSekolah($req){
        $ta  = $req->ta;
        $title  = '';
        $sek     = DB::table('ta_sekolah')->where('id',$req->id_sek)->first();
        $data    = DB::table('ta_kelulusan')->where('tahun',$ta)->where('id_sek',$req->id_sek)->orderBy('nama')->get();
        foreach($data as $dat){
            if($sek->jenjang == 'SMP'){

                $satu   = DB::table('ta_kelulusan_nilai')->where('tahun',$ta)->where('id_sek',$req->id_sek)
                          ->where('id_mapel',1)->where('nisn',$dat->nisn)->value('nilai');
                $dua    = DB::table('ta_kelulusan_nilai')->where('tahun',$ta)->where('id_sek',$req->id_sek)
                          ->where('id_mapel',2)->where('nisn',$dat->nisn)->value('nilai');
                $tiga   = DB::table('ta_kelulusan_nilai')->where('tahun',$ta)->where('id_sek',$req->id_sek)
                          ->where('id_mapel',3)->where('nisn',$dat->nisn)->value('nilai');

                $empat  = DB::table('ta_kelulusan_nilai')->where('tahun',$ta)->where('id_sek',$req->id_sek)
                          ->where('id_mapel',4)->where('nisn',$dat->nisn)->value('nilai');

                $lima   = DB::table('ta_kelulusan_nilai')->where('tahun',$ta)->where('id_sek',$req->id_sek)
                          ->where('id_mapel',5)->where('nisn',$dat->nisn)->value('nilai');

                $enam    = DB::table('ta_kelulusan_nilai')->where('tahun',$ta)->where('id_sek',$req->id_sek)
                          ->where('id_mapel',6)->where('nisn',$dat->nisn)->value('nilai');

                $tujuh   = DB::table('ta_kelulusan_nilai')->where('tahun',$ta)->where('id_sek',$req->id_sek)
                          ->where('id_mapel',7)->where('nisn',$dat->nisn)->value('nilai');

                $delapan    = DB::table('ta_kelulusan_nilai')->where('tahun',$ta)->where('id_sek',$req->id_sek)
                          ->where('id_mapel',8)->where('nisn',$dat->nisn)->value('nilai');

                $sembilan    = DB::table('ta_kelulusan_nilai')->where('tahun',$ta)->where('id_sek',$req->id_sek)
                          ->where('id_mapel',9)->where('nisn',$dat->nisn)->value('nilai');

                $sepuluh    = DB::table('ta_kelulusan_nilai')->where('tahun',$ta)->where('id_sek',$req->id_sek)
                          ->where('id_mapel',10)->where('nisn',$dat->nisn)->value('nilai');
            }elseif($sek->jenjang == 'SD'){

                $satu   = DB::table('ta_kelulusan_nilai')->where('tahun',$ta)->where('id_sek',$req->id_sek)
                          ->where('id_mapel',11)->where('nisn',$dat->nisn)->value('nilai');
                $dua    = DB::table('ta_kelulusan_nilai')->where('tahun',$ta)->where('id_sek',$req->id_sek)
                          ->where('id_mapel',12)->where('nisn',$dat->nisn)->value('nilai');
                $tiga   = DB::table('ta_kelulusan_nilai')->where('tahun',$ta)->where('id_sek',$req->id_sek)
                          ->where('id_mapel',13)->where('nisn',$dat->nisn)->value('nilai');

                $empat  = DB::table('ta_kelulusan_nilai')->where('tahun',$ta)->where('id_sek',$req->id_sek)
                          ->where('id_mapel',14)->where('nisn',$dat->nisn)->value('nilai');

                $lima   = DB::table('ta_kelulusan_nilai')->where('tahun',$ta)->where('id_sek',$req->id_sek)
                          ->where('id_mapel',15)->where('nisn',$dat->nisn)->value('nilai');

                $enam    = DB::table('ta_kelulusan_nilai')->where('tahun',$ta)->where('id_sek',$req->id_sek)
                          ->where('id_mapel',16)->where('nisn',$dat->nisn)->value('nilai');

                $tujuh   = DB::table('ta_kelulusan_nilai')->where('tahun',$ta)->where('id_sek',$req->id_sek)
                          ->where('id_mapel',17)->where('nisn',$dat->nisn)->value('nilai');

                $delapan    = DB::table('ta_kelulusan_nilai')->where('tahun',$ta)->where('id_sek',$req->id_sek)
                          ->where('id_mapel',18)->where('nisn',$dat->nisn)->value('nilai');

                $sembilan    = 0;

                $sepuluh    = 0;
            }else{

                $satu   = 0; $dua    = 0;
                $tiga   = 0; $empat  = 0;
                $lima   = 0; $enam   = 0;
                $tujuh  = 0; $delapan = 0;
                $sembilan = 0; $sepuluh = 0;
            }
            $datas[] = [
              'id'  => $dat->id,
              'nisn'  => $dat->nisn,
              'nama'  => $dat->nama,
              'nik'  => $dat->nik,
              'ttl'  => $dat->tempat_lahir.' / '.$dat->tanggal_lahir,
              'jenis_kelamin'  => $dat->jenis_kelamin,
              'alamat'  => $dat->alamat,
              'is_lulus'  => $dat->is_lulus,
              'satu'  => $satu,
              'dua' => $dua,
              'tiga'  => $tiga,
              'empat' => $empat,
              'lima'  => $lima,
              'enam'  => $enam,
              'tujuh'  => $tujuh,
              'delapan'  => $delapan,
              'sembilan'  => $sembilan,
              'sepuluh'  => $sepuluh,
            ];
        }
        if(!sizeOf($data)) $datas = [];

        if($sek->jenjang == 'SMP'){
            $mapel  = ['PA','PKN','BI','MTK','IPA','IPS','SB','BIG','PRK','PJOK','RATA2'];
        }elseif($sek->jenjang == 'SD'){
            $mapel  = ['PA','PKN','BI','MTK','IPA','IPS','SB','PJOK','RATA2'];
        }else{
            $mapel = [];
        }

        return [
          'req' => $req->all(),
          'data'  => $datas,
          'otoritas'  => HA::GetOtoritas(Auth::id(),3),
          'title' => $title,
          'id_sek'  => $sek->id,
          'mapels' => $mapel,
          'jenjang' => $sek->jenjang
        ];
        return [
          'req' => $req->all(),
          'data'  => [],
          'tex' => 'RekapPerSekolah',
          'otoritas'  => HA::GetOtoritas(Auth::id(),4),
        ];
    }

}
