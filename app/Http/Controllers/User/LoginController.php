<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    /**
     * index
     *
     * @param  mixed $request
     * @return void
     */
    public function login(Request $request)
    {
        //set validasi
        $validator = Validator::make($request->all(), [
            'email'    => 'required',
            'password' => 'required',
        ]);

        //response error validasi
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //get "email" dan "password" dari input
        // $credentials = $request->only('email', 'password');
        $credentials = $request->only('email', 'password');
        //check jika "email" dan "password" tidak sesuai
        if(!$token = auth()->guard('api')->attempt($credentials)) {

            //response login "failed"
            return response()->json([
                'success' => false,
                'message' => 'Username or Password is incorrect',
                'password'  => ['Username or Password is incorrect']
            ], 401);

        }

        //response login "success" dengan generate "Token"
        return response()->json([
            'success' => true,
            'user'    => auth()->guard('api')->user(),
            'token'   => $token
        ], 200);
    }

    /**
     * getUser
     *
     * @return void
     */
    public function getUser()
    {
        //response data "user" yang sedang login
        return response()->json([
            'success' => true,
            'user'    => auth()->guard('api')->user()
        ], 200);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(Request $request)
    {
        //refresh "token"
        $refreshToken = JWTAuth::refresh(JWTAuth::getToken());

        //set user dengan "token" baru
        $user = JWTAuth::setToken($refreshToken)->toUser();

        //set header "Authorization" dengan type Bearer + "token" baru
        $request->headers->set('Authorization','Bearer '.$refreshToken);

        //response data "user" dengan "token" baru
        return response()->json([
            'success' => true,
            'user'    => $user,
            'token'   => $refreshToken,
        ], 200);
    }

    /**
     * logout
     *
     * @return void
     */
    public function logout()
    {
        //remove "token" JWT
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

        //response "success" logout
        return response()->json([
            'success' => true,
        ], 200);

    }

    public function register(Request $req){
        $rules = [
            'password' => ['required', 'confirmed', Password::min(6)],
            'name' => 'required',
            'hp' => 'required|unique:users',
        ];

        $customMessages = [
            'required' => ':attribute wajib diisi.',
            'unique'  => ':attribute sudah terdaftar',
            'confirmed' => ':attribute konfirmasi tidak cocok'
        ];

        if($req->hp == '08139393'){
            $hp  = '081372869393';
            $id  = 'JTM-XXX';
            self::WaRegister($hp,$id);
            self::WaRegInformasi($hp,$id,9);
            return response()->json([
              'wil' => ['Wajib Pilih Salah Satu Wilayah '],
              'req' => $req->all()
            ], 422);
        }

        $validator = Validator::make($req->all(),$rules,$customMessages);

        //response error validasi
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if(!isset($req->wil)){
            return response()->json([
              'wil' => ['Wajib Pilih Salah Satu Wilayah '],
              'req' => $req->all()
            ], 422);
        }

        $hp  = str_replace(['+','-','.',' '],"",$req->hp);
        $len  = strlen($hp);
        $hps  = substr($hp,0,1);
        if($hps == '0'){
          $hps = $hp;
        }else{
          $hps = '0'.substr($hp,2,$len);
        }

        $cek  = DB::table('ta_pelanggan')->where('hp','LIKE','%'.$hps)->first();
        if($cek){
            return response()->json([
              'hp' => ['Nomor HP Sudah Terdaftar '.$hps],
            ], 422);
        }

        if(sizeOf($req->wil)){
            $wilayah  = DB::table('ref_wilayah')->whereIn('id',$req->wil)->get();

        }

        if($req->otp){
            $cek  = DB::table('temp_register')->where('hp',$hps)->first();
            if($cek){
                if($cek->otp != $req->otp){
                    DB::table('wa_send')->insert([
                        'hp'  => $hps,
                        'pesan' => 'Kode Register OTP JastipMan Anda Adalah : '.$cek->otp,
                        'prioritas' => 1,
                        'created_by'  => 'Admin OTP'
                    ]);

                    return response()->json([
                      'otp' => ['OTP Salah (OTP Anda Sudah Dikirim Melalui Whatsapp ke Nomor '.$hps.') '],
                      'req' => $req->all()
                    ], 422);
                }
            }else{
                $otp  = rand(1000,9999);
                DB::table('temp_register')->insert([
                    'hp'  => $hps,
                    'otp' => $otp,
                ]);
                DB::table('wa_send')->insert([
                    'hp'  => $hps,
                    'pesan' => 'Kode Register OTP JastipMan Anda Adalah : '.$otp,
                    'prioritas' => 1,
                    'created_by'  => 'Admin OTP'
                ]);
                return response()->json([
                  'otp' => ['OTP Salah (OTP Anda Sudah Dikirim Melalui Whatsapp ke Nomor '.$hps.')'],
                  'req' => $req->all()
                ], 422);
            }

        }else{
            $otp  = rand(1000,9999);
            $cek  = DB::table('temp_register')->where('hp',$hps)->first();
            if($cek){
                DB::table('temp_register')->where('hp',$hps)->update([
                    'otp' => $otp,
                ]);
            }else{
                DB::table('temp_register')->insert([
                    'hp'  => $hps,
                    'otp' => $otp,
                ]);
            }
            DB::table('wa_send')->insert([
                'hp'  => $hps,
                'pesan' => 'Kode Register OTP JastipMan Anda Adalah : '.$otp,
                'prioritas' => 1,
                'created_by'  => 'Admin OTP'
            ]);
            return response()->json([
              'otp' => ['Silahkan ISI OTP (OTP Anda Sudah Dikirim Melalui Whatsapp ke Nomor '.$hps.')'],
              'req' => $req->all()
            ], 422);
        }

        //get "email" dan "password" dari input
        $credentials = $req->only('email', 'password','hp','name','username');
        $slug  =  bin2hex(random_bytes(5));
        $slug_pel  =  bin2hex(random_bytes(5));
        $wilayah  = DB::table('ref_wilayah')->whereIn('id',$req->wil)->get();
        DB::beginTransaction();
        try {
            User::create([
                'name'  => $req->name,
                'username' => $hps,
                'email' => $hps.'@gmail.com',
                'password'  => Hash::make($req->password),
                'hp'  => $hps,
                'aktivasi'  => 1,
                'slug'  => $slug
            ]);
            DB::table('ta_pelanggan')->insert([
                'slug'  => $slug_pel,
                'user_slug' => $slug,
                'user_email'  => $hps.'@gmail.com',
                'nama'  => $req->name,
                'hp'  => $hps,

            ]);

            foreach($wilayah as $wils){
                $cek    = DB::table('ta_pelanggan_id')->where('kode_wilayah',$wils->kode)->orderBy('nomor','desc')->first();
                if($cek){
                    $id_pel = str_replace("JASTIPMAN-".$wils->kode,"","$cek->id_pelanggan");
                    $id_pel = $cek->nomor;
                    $nomor  = (int)$id_pel + 1;
                    $len    = strlen($nomor);
                    if($len == 1) $id_pel = 'JASTIPMAN-'.$wils->kode.'00'.$nomor;
                    elseif($len == 2) $id_pel = 'JASTIPMAN-'.$wils->kode.'0'.$nomor;
                    else $id_pel = 'JASTIPMAN-'.$wils->kode.$nomor;
                }else{
                    $nomor = 1;
                    $id_pel = 'JASTIPMAN-'.$wils->kode.'001';
                }


                $slug1  =  bin2hex(random_bytes(5));
                DB::table('ta_pelanggan_id')->insert([
                    'slug'  => $slug1,
                    'slug_pelanggan'  => $slug_pel,
                    'kode_wilayah'  => $wils->kode,
                    'wilayah' => $wils->nama,
                    'nama'  => $req->name,
                    'nomor' => $nomor,
                    'id_pelanggan'  => $id_pel,
                    'created_by'  => 'By Register',
                    'updated_by'  => 'By Register',
                ]);


                $message = [
                  'Hi '.$req->name,
                  'Anda Berhasil Register',
                  'ID Pelanggan Anda Untuk '.$wils->keterangan.' Adalah : '.$id_pel,
                  'Silahkan input ke aplikasi belanja online dengan format di bawah:',
                  'Nama : '.$id_pel,
                  'No Telp Penerima : 081210939136',
                  'Provinsi : DKI Jakarta',
                  'Kota/Kabupaten : Kota Jakarta Utara',
                  'Kecamatan : Koja',
                  'Kode Pos : 14230',
                  'Alamat Lengkap : Jl. Bandar II Nomor 25e, RT.6/RW.6 Kelurahan Rawa Badak Selatan, Kecamatan Koja, Kota Jakarta Utara, DKI Jakarta 14230 ('.$hps.')',
                  'Untuk Bantuan dapat menghubungi admin JasTipMan di 0812-9277-9417',
                  '',
                  'Terimakasih'
                ];
                $message = implode("\n", $message);
                DB::table('wa_send')->insert([
                    'hp'  => $hps,
                    'pesan' => $message,
                    'prioritas' => 1,
                    'created_by'  => 'By Register'
                ]);

                self::WaRegInformasi($hps,$id_pel,$wils->id);
            }


            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['otp'=>[
              'Error Register, Hubungi Administrator 2003',
              $e
              ]], 422);
        }

        //response login "success" dengan generate "Token"
        self::WaRegister($hps,$req->name);

        return response()->json([
            'success' => true,
            // 'user'    => auth()->guard('api')->user(),
            // // 'token'   => $token,
            // 'req' => $req->all()
        ], 200);
    }

    public function LostPassword(Request $req){
        if(!$req->hp){
            return response()->json(['hp'=>[
              'Masukkan Nomor HP',
            ]], 422);
        }

        $cek  = DB::table('users')->where('username',$req->hp)->first();
        if($cek){
              if(!$req->otp){
                    $otp  = rand(1000,9999);
                    DB::table('wa_send')->insert([
                        'hp'  => $req->hp,
                        'pesan' => 'Kode OTP Lupa Password Adalah : '.$otp,
                        'created_by'  => 'Admin Lupa Password'
                    ]);
                    DB::table('users')->where('username',$req->hp)->update([
                        'otp' => $otp,
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Masukkan Kode OTP',
                        'otp'  => ['Masukkan Kode OTP (Dikirim Via WhatsApp) '],
                        'req' => $req->all()
                    ], 200);
              }

              if($req->otp != $cek->otp){
                    DB::table('wa_send')->insert([
                        'hp'  => $req->hp,
                        'pesan' => 'Kode OTP Lupa Password Adalah : '.$cek->otp,
                        'created_by'  => 'Admin Lupa Password'
                    ]);
                    return response()->json([
                        'success' => false,
                        'otp' => ['Kode OTP Salah '],
                        'req' => $req->all()
                    ], 200);
              }
              $password  = rand(1000,9999);
              DB::table('users')->where('username',$req->hp)->update([
                  'password'  => Hash::make($password),
              ]);

              $message = [

                  '',
                  '',
                  'Hi '.$cek->name,
                  'Berikut Data Akses Aplikasi JasTipMan',
                  'Username : '.$req->hp,
                  'Password : '.$password,
                  'Silahkan Login ke Alamat http://jastipman.id',
                  'Terimakasih',
                  '',
                  '*JASA TITIPAN MAN*',
                  '🛒 *_Shopping Delivery Partner_*',

              ];
              $message = implode("\n", $message);

              DB::table('wa_send')->insert([
                  'hp'  => $req->hp,
                  'pesan' => $message,
                  'created_by'  => 'Admin Register Users'
              ]);


              return response()->json([
                  'success' => true,
                  'otp' => ['Password Anda Berhasil Direset dan sudah dikirim via Whatsapp, Terimakasih'],
                  'req' => $req->all()
              ], 200);

        }else{
            return response()->json(['hp'=>[
              'Nomor HP Tidak Terdaftar',
            ]], 422);
        }

        return response()->json([
            'success' => false,
            'message' => 'Lost Password',
            'req' => $req->all()
        ], 200);
    }

    static function WaRegister($hp,$id_pel){
          $message = [
              '',
              '',
              'Hi '.$id_pel,
              'Ini Adalah Contoh Pengisian Alamat Pada Aplikasi Belanja Online',
              'Terimakasih',
              '',
              '*JASA TITIPAN MAN*',
              '🛒 *_Shopping Delivery Partner_*',

          ];
          $message = implode("\n", $message);

          // DB::table('wa_send')->insert([
          //     'hp'  => $hp,
          //     'pesan' => $message,
          //     'image' => 'image/reg1.jpeg',
          //     'created_by'  => 'Admin Register Users'
          // ]);


          $message = [
              '',
              '',
              'Hi '.$id_pel,
              'Ini Adalah Contoh Pengisian Alamat Pada Aplikasi Belanja Online',
              'Terimakasih',
              '',
              '*JASA TITIPAN MAN*',
              '🛒 *_Shopping Delivery Partner_*',

          ];
          $message = implode("\n", $message);
          // DB::table('wa_send')->insert([
          //     'hp'  => $hp,
          //     'pesan' => $message,
          //     'image' => 'image/reg2.jpeg',
          //     'created_by'  => 'Admin Register Users'
          // ]);


    }

    static function WaRegInformasi($hp,$id_pel,$id_wil){

          if($id_wil == 1){
              // Tarempa
              $message = [
                  'Hi '.$id_pel,
                  '*Biaya Pengiriman via Kapal Tol Laut Jakarta - Tarempa:*',
                  '🛍 Kategori Berat 1 kg s.d 15 kg/resi = Rp.10.000/kg.',
                  '↔️ Range 16 kg s.d 25 kg /resi = Rp.150.000.',
                  '↔️ Range 26 kg s.d 75 kg /resi = Rp.6.000/kg.',
                  '↔️ Range 76 kg s.d 100 kg /resi = Rp.450.000.',
                  '🛒 Kategori Berat > 100 kg/resi = Rp. 4.500/kg.',
                  '',
                  'Keramik Rp. 20.000/kotak;',
                  'Granit Rp. 30.000/kotak;',
                  'Makanan Kucing Rp. 90.000/kg;',
                  'Sepeda Listrik Rp. 250.000/ unit;',
                  '',
                  '✍ Ketentuan:',
                  '⛴️ Pengiriman menggunakan kapal Swasta I, Swasta II, Swasta III & Swasta IV hanya untuk berat barang < 16 kg/item dan dengan tarif paling tinggi disemua kategori, kecuali ada permintaan dari Mitra;',
                  '🔢 Biaya/Berat dihitung per Item Barang atau per Resi;',
                  '🔝 Biaya/Berat per item barang dihitung dengan pembulatan keatas per 1 kg;',
                  '🎈 Ongkir dihitung minimal 1 kg untuk Pengiriman Barang dari Jakarta;',
                  '📏 Barang-barang yang ringan namun berongga dihitung Berat Volume, perhitungan Berat Volume = Panjang (cm) x Lebar (cm) x Tinggi (cm) ÷ 6000;',
                  '✅ Biaya pengiriman barang diambil yang terbesar dari cara/sistem perhitungan barang yang digunakan;',
                  '🖐 Tidak menerima pembelian barang sistem COD; ',
                  '🆘 Jika terjadi keadaan Kahar/musibah seperti kapal tenggelam, terbakar, gudang terbakar dan keadaan musibah lainnya yang bukan disebabkan kelalaian kami tidak menjadi tanggung jawab kami. ',
                  '',
                  '',
                  '🗓 Jadwal Kapal:',
                  '- Kapal Tol Laut 1 Bulan sekali;',
                  '- Kapal Swasta minimal 4 Kali sebulan;',
                  '- Jadwal kapal akan diberitahukan secara berkala;',
                  '- Jadwal kapal dapat berubah sewaktu-waktu.',
                  '',
                  '',
                  '🪀 Customer Service  ',
                  '- 081210939136',
                  '- 081275428170',
                  '',
                  '',
                  '🪀 Persons Contact ',
                  '- 081280013712 Hardi (Jakarta) ',
                  '- 081292779417 Puy (Anambas) ',
                  '',
                  '',
                  '🪀 Perwakilan JasTipMan',
                  'Tarempa',
                  '082116772526 Robi',
                  'Palmatak:',
                  '082384527288 Suziana',
                  '081261936833 Ayoni',
                  'Air Asuk:',
                  '081268181167 Neliyanti',
                  '082327761229 Icha',
                  'Siantan Utara:',
                  '082268594096 Rusli Andari',
                  'Kute Siantan:',
                  '082169445959 Alan',
                  'Siantan Timur:',
                  '085289028889 Romi',
                  'Jemaja:',
                  '085365885673 Amrul ',
                  '',
                  '🛍 Selamat Berbelanja...',
                  '',
                  '🤝 Terimakasih',
                  '',
                  '*JASA TITIPAN MAN*',
                  '🛒 *_Shopping Delivery Partner_*',
              ];
              $message = implode("\n", $message);
              DB::table('wa_send')->insert([
                  'hp'  => $hp,
                  'pesan' => $message,
                  'created_by'  => 'Admin Register Users'
              ]);
          }elseif($id_wil == 9){
              // Letung

              $message = [
                  'Hi '.$id_pel,
                  '*Biaya Pengiriman via Kapal Tol Laut Jakarta - Jemaja:*',
                  '🛍 Kategori Berat 1 kg s.d 15 kg/resi = Rp.15.000/kg.',
                  '↔️ Range 16 kg s.d 25 kg /resi = Rp.225.000.',
                  '🛒 Kategori Berat > 25 kg/resi = Rp. 9.000/kg.',
                  '',
                  'Keramik Rp. 35.000/kotak;',
                  'Granit Rp. 45.000/kotak;',
                  'Makanan Kucing Rp. 120.000/kg;',
                  'Sepeda Listrik Rp. 375.000/ unit;',
                  '',
                  '✍ Ketentuan:',
                  '⛴️ Pengiriman menggunakan kapal Swasta I, Swasta II, Swasta III & Swasta IV hanya untuk berat barang < 16 kg/item dan dengan tarif paling tinggi disemua kategori, kecuali ada permintaan dari Mitra;',
                  '🔢 Biaya/Berat dihitung per Item Barang atau per Resi;',
                  '🔝 Biaya/Berat per item barang dihitung dengan pembulatan keatas per 1 kg;',
                  '🎈 Ongkir dihitung minimal 1 kg untuk Pengiriman Barang dari Jakarta;',
                  '📏 Barang-barang yang ringan namun berongga dihitung Berat Volume, perhitungan Berat Volume = Panjang (cm) x Lebar (cm) x Tinggi (cm) ÷ 6000;',
                  '✅ Biaya pengiriman barang diambil yang terbesar dari cara/sistem perhitungan barang yang digunakan;',
                  '🖐 Tidak menerima pembelian barang sistem COD; ',
                  '🆘 Jika terjadi keadaan Kahar/musibah seperti kapal tenggelam, terbakar, gudang terbakar dan keadaan musibah lainnya yang bukan disebabkan kelalaian kami tidak menjadi tanggung jawab kami. ',
                  '',
                  '',
                  '🗓 Jadwal Kapal:',
                  '- Kapal Tol Laut 1 Bulan sekali;',
                  '- Kapal Swasta minimal 4 Kali sebulan;',
                  '- Jadwal kapal akan diberitahukan secara berkala;',
                  '- Jadwal kapal dapat berubah sewaktu-waktu.',
                  '',
                  '',
                  '🪀 Customer Service  ',
                  '- 081210939136',
                  '- 081275428170',
                  '',
                  '',
                  '🪀 Persons Contact ',
                  '- 081280013712 Hardi (Jakarta) ',
                  '- 081292779417 Puy (Anambas) ',
                  '',
                  '',
                  '🪀 Perwakilan JasTipMan',
                  'Tarempa',
                  '082116772526 Robi',
                  'Palmatak:',
                  '082384527288 Suziana',
                  '081261936833 Ayoni',
                  'Air Asuk:',
                  '081268181167 Neliyanti',
                  '082327761229 Icha',
                  'Siantan Utara:',
                  '082268594096 Rusli Andari',
                  'Kute Siantan:',
                  '082169445959 Alan',
                  'Siantan Timur:',
                  '085289028889 Romi',
                  'Jemaja:',
                  '085365885673 Amrul ',
                  '',
                  '🛍 Selamat Berbelanja...',
                  '',
                  '🤝 Terimakasih',
                  '',
                  '*JASA TITIPAN MAN*',
                  '🛒 *_Shopping Delivery Partner_*',
              ];
              $message = implode("\n", $message);
              DB::table('wa_send')->insert([
                  'hp'  => $hp,
                  'pesan' => $message,
                  'created_by'  => 'Admin Register Users'
              ]);

          }else{
              // Selain yg diatas

              $message = [
                  'Hi '.$id_pel,
                  '*Biaya Pengiriman via Kapal Tol Laut Jakarta - Palmatak / Kute / Air Asuk / Nyamuk / Piasan:*',
                  '🛍 Kategori Berat 1 kg s.d 15 kg/resi = Rp.12.000/kg.',
                  '↔️ Range 16 kg s.d 25 kg /resi = Rp.180.000.',
                  '🛒 Kategori Berat > 25 kg/resi = Rp. 7.000/kg.',
                  '',
                  'Keramik Rp. 25.000/kotak;',
                  'Granit Rp. 35.000/kotak;',
                  'Makanan Kucing Rp. 100.000/kg;',
                  'Sepeda Listrik Rp. 350.000/ unit;',
                  '',
                  '✍ Ketentuan:',
                  '⛴️ Pengiriman menggunakan kapal Swasta I, Swasta II, Swasta III & Swasta IV hanya untuk berat barang < 16 kg/item dan dengan tarif paling tinggi disemua kategori, kecuali ada permintaan dari Mitra;',
                  '🔢 Biaya/Berat dihitung per Item Barang atau per Resi;',
                  '🔝 Biaya/Berat per item barang dihitung dengan pembulatan keatas per 1 kg;',
                  '🎈 Ongkir dihitung minimal 1 kg untuk Pengiriman Barang dari Jakarta;',
                  '📏 Barang-barang yang ringan namun berongga dihitung Berat Volume, perhitungan Berat Volume = Panjang (cm) x Lebar (cm) x Tinggi (cm) ÷ 6000;',
                  '✅ Biaya pengiriman barang diambil yang terbesar dari cara/sistem perhitungan barang yang digunakan;',
                  '🖐 Tidak menerima pembelian barang sistem COD; ',
                  '🆘 Jika terjadi keadaan Kahar/musibah seperti kapal tenggelam, terbakar, gudang terbakar dan keadaan musibah lainnya yang bukan disebabkan kelalaian kami tidak menjadi tanggung jawab kami. ',
                  '',
                  '',
                  '🗓 Jadwal Kapal:',
                  '- Kapal Tol Laut 1 Bulan sekali;',
                  '- Kapal Swasta minimal 4 Kali sebulan;',
                  '- Jadwal kapal akan diberitahukan secara berkala;',
                  '- Jadwal kapal dapat berubah sewaktu-waktu.',
                  '',
                  '',
                  '🪀 Customer Service  ',
                  '- 081210939136',
                  '- 081275428170',
                  '',
                  '',
                  '🪀 Persons Contact ',
                  '- 081280013712 Hardi (Jakarta) ',
                  '- 081292779417 Puy (Anambas) ',
                  '',
                  '',
                  '🪀 Perwakilan JasTipMan',
                  'Tarempa',
                  '082116772526 Robi',
                  'Palmatak:',
                  '082384527288 Suziana',
                  '081261936833 Ayoni',
                  'Air Asuk:',
                  '081268181167 Neliyanti',
                  '082327761229 Icha',
                  'Siantan Utara:',
                  '082268594096 Rusli Andari',
                  'Kute Siantan:',
                  '082169445959 Alan',
                  'Siantan Timur:',
                  '085289028889 Romi',
                  'Jemaja:',
                  '085365885673 Amrul ',
                  '',
                  '🛍 Selamat Berbelanja...',
                  '',
                  '🤝 Terimakasih',
                  '',
                  '*JASA TITIPAN MAN*',
                  '🛒 *_Shopping Delivery Partner_*',

              ];

              $message = implode("\n", $message);
              DB::table('wa_send')->insert([
                  'hp'  => $hp,
                  'pesan' => $message,
                  'created_by'  => 'Admin Register Users'
              ]);

          }

    }

}
