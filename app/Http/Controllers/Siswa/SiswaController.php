<?php

namespace App\Http\Controllers\Siswa;

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


class SiswaController extends Controller{

    static function index($req){
        return view('siswa.index');
    }

    static function IndexPR(){
        return view('siswa.index_pr');
    }

    static function GetDataSiswa($url,$req){
        $split  = explode("_", $url);
        $id     = $split[0];

        $data  = DB::table('ta_siswas')->where('id',$id)->first();
        if($data){
            $respon  = [
              'id'    => $data->id,
              'nama'  => $data->nama,
              'nis' => $data->nis,
              'id_rombel' => $data->id_rombel,
              'id_kelas'  => $data->id_kelas,
              'ta'  => $data->ta,
              'kd_ta' => $data->ta,
              'thn_ajaran'  => $data->ta,
            ];
            $count = 1;
            $pesan = 'Data Di';
        }else{
          $respon  = [];
          $count = 0;
          $pesan = 'Data Tidak Ditemukan';
        }
        return response()->json(['data'=>$respon,'count'=>$count, 'pesan'=>$pesan ]);
    }

    static function GetDataSiswas(){
        $id_user  = Auth::id();
        $operator  = DB::table('ta_sekolah_opr')->where('id_user',$id_user)->where('status',1)->first();
        if($operator){
            $sekolah  = DB::table('ta_sekolah')->where('id',$operator->id_sek)->first();
            if($sekolah){
                $data = DB::table('ta_siswas')->where('id_sek',$sekolah->id)->where('status',1)->orderBy('id','desc')->get();
                if(sizeOf($data)){
                    $no = 1;
                    foreach($data as $dat){
                        $cek  = DB::table('ta_siswas_user as siswa')->select('users.name')
                                ->join('users','users.id','siswa.id_user')
                                ->where('siswa.id_siswa',$dat->id)->first();
                        if($cek){
                            $user  = $cek->name;
                        }else{
                            $user  = '-';
                        }
                        $respon[]  = [
                          'id'    => $dat->id,
                          'id_sek'=> $sekolah->id,
                          'urut'  => $no,
                          'nama'  => $dat->nama,
                          'nis' => $dat->nis,
                          'nm_sekolah'  => $sekolah->nama,
                          'user'  => $user,
                          'kelas' => $dat->ur_kelas,
                          'rombel'  => $dat->ur_rombel
                        ];
                        $no++;
                    }
                }else{
                    $respon  = [];
                }
            }else{
              $respon  = [];
            }
            $otoritas = 1;
        }else{
              $respon = [];
              $otoritas = 0;
        }
        return response()->json(['data'=>$respon, 'otoritas'=>$otoritas ]);

    }

    static function TambahDataSiswa($req){
        $id_user  = Auth::id(); $otoritas = 0; $id_sek  = 0;
        $id_sek  =  Ta_Sekolah_Opr::where('id_user',$id_user)->value('id_sek');
        if(!$id_sek){
            $id_sek  = Ta_Sekolah::where('id_kepsek',$id_user)->value('id');
        }
        $sekolah  = Ta_Sekolah::where('id',$id_sek)->first();
        if($sekolah) $otoritas = 1;


        if($otoritas){
            if(\Request::isMethod('POST')){
                $kelas  = Ta_Kelas::where('id',$req->id_kelas)->value('uraian');
                $rombel  = Ta_Rombel::where('id',$req->id_rombel)->where('id_rk',$req->id_kelas)->value('uraian');
                $nis  = $req->nis;
                $cek  = DB::table('ta_siswas')->where('nis',$nis)->where('id_sek',$sekolah->id)->first();
                if(!$cek){
                    DB::table('ta_siswas')->insertGetId([
                        'nama'    => $req->nama,
                        'nis'      => $req->nis,
                        'id_sek'  => $sekolah->id,
                        'id_kelas'  => $req->id_kelas,
                        'id_rombel' => $req->id_rombel,
                        'ur_kelas'  => $kelas,
                        'ur_rombel' => $rombel,
                        'ta'  => $req->kd_ta,
                    ]);
                }else{
                    DB::table('ta_siswas')->where('id',$cek->id)->update([
                      'status'=>1,
                      'id_kelas'  => $req->id_kelas,
                      'id_rombel' => $req->id_rombel,
                      'ur_kelas'  => $kelas,
                      'ur_rombel' => $rombel,
                      'ta'  => $req->kd_ta
                    ]);
                }
                $data   = self::GetDataSiswas();
                $respon = ['pesan'=>'Data Siswa '.$req->nama.' Berhasil ditambah di '.$sekolah->nama, 'data'=>$data];
                return response()->json(['data'=>$respon,'error'=>0 ]);
            }
        }else{
            return response()->json(['pesan'=>'Otoritas User Tidak Diizinkan','error'=>1 ]);
        }
    }

    static function UpdateDataSiswa($req){
        $id  = $req->id;
        $nis = $req->nis;
        $cek = DB::table('ta_siswas')->where('id','!=',$id)->where('nis',$nis)->count();
        if(!$cek){
            $kelas  = Ta_Kelas::where('id',$req->id_kelas)->value('uraian');
            $rombel  = Ta_Rombel::where('id',$req->id_rombel)->where('id_rk',$req->id_kelas)->value('uraian');
            DB::table('ta_siswas')->where('id',$id)->update([
              'nama'  => $req->nama,
              'nis'   => $req->nis,
              'id_rombel' => $req->id_rombel,
              'id_kelas'  => $req->id_kelas,
              'ur_rombel' => $rombel,
              'ur_kelas'  => $kelas
            ]);
        }

        return  [
          'data'    => self::GetDataSiswas(),
          'error'   => 0,
          'pesan'   => 'Berhasil Update Data'
        ];
    }

    static function HapusDataSiswa($req){
        DB::table('ta_siswas')->where('id',$req->id_)->update(['status'=>0]);
        return self::GetDataSiswas();
    }

    static function UpdateDataUser($req){
        $request  = $req;
        $id_user  = Auth::id();
        $sekolah = DB::table('ta_sekolah')->where('id_kepsek',$id_user)->first();
        if(!$sekolah){
            $respon = ['pesan'=>'Anda Tidak Sebagai Kepala Sekolah'];
            return response()->json(['data'=>$respon,'error'=>1 ]);
        }
        if(\Request::isMethod('POST')){
            $id   = $req->data['id_user'];
            $cek  = DB::table('ta_siswas_user')->where('id_user',$id)->first();
            if($cek){
                DB::table('ta_siswas_user')->where('id_user',$id)->update([
                    'id_siswa'    => $req->data['id_siswa'],
                    'id_kelas'    => $req->data['id_kelas'],
                    'id_rombel'   => $req->data['id_rombel'],
                ]);
                $data   = self::GetDataSiswas();
                return ['pesan'=>'Update Data User Berhasil', 'data'=>$data, 'error'=>0];
            }else{
                DB::table('ta_siswas_user')->insert([
                    'id_user'    => $req->data['id_user'],
                    'id_siswa'    => $req->data['id_siswa'],
                    'id_kelas'    => $req->data['id_kelas'],
                    'id_rombel'    => $req->data['id_rombel'],
                ]);
                $data   = self::GetDataSiswas();
                return ['pesan'=>'Tambah Data User Berhasil', 'error'=>0, 'data'=>$data];
            }

        }
    }

    static function FilterSatu($req){
        $otoritas  = HA::GetOtoritas(Auth::id(),3);
        if($req->id_kec == 0){
            $query = DB::table('ta_siswas')->where('status',1)->orderByRaw('id_kelas ASC','nama ASC');
            $query->where('ta',$req->ta);
            if($req->jenjang){
                if($req->jen_sek){
                    $sekolah  = DB::table('ta_sekolah')->select('id','jenjang')->where('jen_sek',$req->jen_sek)->where('jenjang',$req->jenjang)->get();
                }else{
                    $sekolah  = DB::table('ta_sekolah')->select('id','jenjang')->where('jenjang',$req->jenjang)->get();
                }
                foreach($sekolah as $dat){
                    $jenjang[] = $dat->jenjang;
                    $id_sek[] = $dat->id;
                }
                if(sizeOf($sekolah)){
                    $query->whereIn('id_sek',$id_sek);
                }
            }else{
                if($req->jen_sek){
                    $sekolah  = DB::table('ta_sekolah')->select('id','jenjang')->where('jen_sek',$req->jen_sek)->get();
                    foreach($sekolah as $dat){
                        $id_sek[] = $dat->id;
                    }
                    if(sizeOf($sekolah)){
                        $query->whereIn('id_sek',$id_sek);
                    }
                }
            }
            if($req->id_kelas) $query->where('id_kelas',$req->id_kelas);
            if($req->id_rombel) $query->where('id_rombel',$req->id_rombel);
            $data  = $query->get();

            $no = 1;
            foreach($data as $dat){
                $jk = 'Laki - Laki';
                if($dat->jk == 'P') $jk = 'Perempuan';
                $datas[]  = [
                  'id'  => $dat->id,
                  'urut'  => $no,
                  'nama'  => $dat->nama,
                  'nis' => $dat->nis,
                  'kelas' => $dat->ur_kelas,
                  'rombel'  => $dat->ur_rombel,
                  'jk'  => $jk,
                ];
                $no++;
            }
            if(!sizeOf($data)) $datas = [];
            return [
              'req' => $req->all(),
              'data'  => $datas,
              'otoritas'  => $otoritas,
            ];

        }elseif($req->id_sek){
            $query = DB::table('ta_siswas')->where('id_sek',$req->id_sek)->where('status',1)->orderByRaw('id_kelas ASC','nama ASC');
            $query->where('ta',$req->ta);
            if($req->ta) $query->where('ta',$req->ta);
            if($req->id_kelas) $query->where('id_kelas',$req->id_kelas);
            if($req->id_rombel) $query->where('id_rombel',$req->id_rombel);
            $data  = $query->get();
        }else{
            // semua kecamatan
            $query  = DB::table('ta_sekolah')->select('id','jenjang')->where('id_kec',$req->id_kec);
            if($req->jen_sek) $query->where('jen_sek',$req->jen_sek);
            $sekolah = $query->get();
            foreach($sekolah as $dat){
                $jenjang[] = $dat->jenjang;
                $id_sek[] = $dat->id;
            }
            if(!sizeOf($sekolah)){
                $jenjang = []; $id_sek = [];
            }

            if($req->jenjang){
                $data = DB::table('ta_siswas')->where('ta',$req->ta)->whereIn('id_sek',$id_sek)->where('status',1)->orderBy('id','desc')->get();
            }else{
                $data = DB::table('ta_siswas')->where('ta',$req->ta)->whereIn('id_sek',$id_sek)->where('status',1)->orderBy('id','desc')->get();
            }

            $query = DB::table('ta_siswas')->whereIn('id_sek',$id_sek)->where('status',1)->orderByRaw('id_kelas ASC','nama ASC');
            $query->where('ta',$req->ta);
            if($req->id_kelas) $query->where('id_kelas',$req->id_kelas);
            if($req->id_rombel) $query->where('id_rombel',$req->id_rombel);
            $data  = $query->get();

            $no = 1;
            foreach($data as $dat){
                $jk = 'Laki - Laki';
                if($dat->jk == 'P') $jk = 'Perempuan';
                $datas[]  = [
                  'id'  => $dat->id,
                  'urut'  => $no,
                  'nama'  => $dat->nama,
                  'nis' => $dat->nis,
                  'kelas' => $dat->ur_kelas,
                  'rombel'  => $dat->ur_rombel,
                  'jk'  => $jk,
                ];
                $no++;
            }
            if(!sizeOf($data)) $datas = [];
            return [
              'req' => $req->all(),
              'data'  => $datas,
              'otoritas'  => $otoritas,
            ];
        }

        $sekolah  = DB::table('ta_sekolah')->where('id',$req->id_sek)->first();
        if(!$sekolah){
            return [
              'otoritas'  => ['lihat'=>0],
              'data'  => [],
              'req' => $req->all()
            ];
        }

        if(sizeOf($data)){
            $no = 1;
            foreach($data as $dat){
                $cek  = DB::table('ta_siswas_user as siswa')->select('users.name')
                        ->join('users','users.id','siswa.id_user')
                        ->where('siswa.id_siswa',$dat->id)->first();
                if($cek){
                    $user  = $cek->name;
                }else{
                    $user  = '-';
                }
                $jk = 'Laki - Laki';
                if($dat->jk == 'P') $jk = 'Perempuan';
                $respon[]  = [
                  'id'    => $dat->id,
                  'urut'  => $no,
                  'nama'  => $dat->nama,
                  'nis' => $dat->nis,
                  'user'  => $user,
                  'kelas' => $dat->ur_kelas,
                  'rombel'  => $dat->ur_rombel,
                  'jk'  => $jk,
                ];
                $no++;
            }
        }else{
            $respon  = [];
        }

        return [
          'req' => $req->all(),
          'data'  => $respon,
          'otoritas'  => $otoritas,
        ];
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
        return [
          'req' => $req->all(),
          'data'  => $datas,
          'otoritas'  => $otoritas,
        ];
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

    static function GuestGetSiswaAkhir($req){
        $error = 1; $pesan = 'Data Tidak Ditemukan';
        if(strlen($req->nisn) >= 4){
            $data  = DB::table('dapodik_siswa_akhir')->orWhere('nisn',$req->nisn)->orWhere('nik',$req->nisn)->get();
            foreach($data as $dat){
                $datas[] = [
                  'id'  => $dat->id,
                  'nama'  => $dat->nama,
                  'nisn'  => $dat->nisn,
                  'nik'  => $dat->nik,
                  'nm_ayah'  => $dat->nama_ayah,
                  'nik_ayah'  => '',
                  'alamat'  => $dat->alamat_jalan.' RT/RW: '.$dat->rt.'/'.$dat->rw,
                  'ttl' => $dat->tempat_lahir.' / '.$dat->tanggal_lahir,
                  'nm_ibu'  => $dat->nama_ibu_kandung,
                  'sek_tujuan'  => 'Nama Sekolah',
                ];
            }
            if(!sizeOf($data)) $datas = [];
            else{
                $error = 0; $pesan = 'Silahkan Pilih Data';
            }
        }else{
            $datas = [];
        }

        return response()->json([
            'success'  => false,
            'message' => $pesan,
            'error' => $error,
            'pesan' => $pesan,
            'data'  => $datas,
            'req' => $req->all()
        ]);
        
    }

    static function GetDataKelulusan($req){
        if(\Request::isMethod('POST')){
            $title  = '';
            $opr   = DB::table('ta_sekolah_opr')->where('id_user',Auth::id())->where('status',1)->first();
            if(!$opr){
                return [
                  'otoritas'  => ['lihat'=>0],
                  'title' => 'Otoritas Operator Belum Disetting, Hubungi Administrator'
                ];
            }
            $sek     = DB::table('ta_sekolah')->where('id',$opr->id_sek)->first();
            $sek_dik = DB::table('dapodik_siswa_akhir')->where('sekolah_id',$sek->sekolah_id)->where('ta',$req->ta)->get();
            foreach($sek_dik as $dat){
                $cek = DB::table('ta_kelulusan')->where('tahun',$req->ta)->where('id_sek',$sek->id)->where('nisn',$dat->nisn)->first();
                if(!$cek){
                    $alamat  = $dat->alamat_jalan;
                    if($dat->rt) $alamat .= ' RT: '.$dat->rt;
                    if($dat->rw) $alamat .= ' RW: '.$dat->rw;
                    DB::table('ta_kelulusan')->insert([
                        'tahun' => $req->ta,
                        'id_sek'  => $sek->id,
                        'nisn'  => $dat->nisn,
                        'nama'  => $dat->nama,
                        'nik'  => $dat->nik,
                        'tempat_lahir'  => $dat->tempat_lahir,
                        'tanggal_lahir'  => $dat->tanggal_lahir,
                        'jenis_kelamin'  => $dat->jenis_kelamin,
                        'alamat'  => $alamat,
                    ]);
                }
            }
            if(!sizeOf($sek_dik)){
                $title  .= $sek->nama.' (Data Dapodik Tidak Tersedia)';
            }else{
                $title  .= $sek->nama;
            }
            $urut = 1;
            $data    = DB::table('ta_kelulusan')->where('tahun',$req->ta)->where('id_sek',$opr->id_sek)->orderBy('nama')->get();
            foreach($data as $dat){
                $datas[] = [
                  'id'  => $dat->id,
                  'urut'  => $urut,
                  'nisn'  => $dat->nisn,
                  'nama'  => $dat->nama,
                  'nik'  => $dat->nik,
                  'ttl'  => $dat->tempat_lahir.' / '.$dat->tanggal_lahir,
                  'jenis_kelamin'  => $dat->jenis_kelamin,
                  'alamat'  => $dat->alamat,
                  'is_lulus'  => $dat->is_lulus
                ];
                $urut++;
            }
            if(!sizeOf($data)) $datas = [];

            return [
              'req' => $req->all(),
              'data'  => $datas,
              'otoritas'  => HA::GetOtoritas(Auth::id(),3),
              'title' => $title,
              'id_sek'  => $sek->id,
            ];
        }
    }

}
