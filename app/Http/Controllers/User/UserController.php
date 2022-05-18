<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\User\HakAksesController as HA;

class UserController extends Controller
{
    public function login(Request $request)
    {

        $credentials = $request->only('email', 'password');
        try {
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])){
                $user = Auth::user();
                $customClaims = ['kid' => 'glxbiIUYMXS3FOjLk0sAdtIZfFbOYssZ', 'user_id' => $user->id];
                $token = JWTAuth::claims($customClaims)
                ->attempt($credentials);

            }

            // if (! $token = JWTAuth::attempt($credentials)) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Username atau Password Salah ',
            //     ], 400);
            // }


            else{
                return response()->json([
                    'success' => false,
                    'message' => 'Username atau Password Salah.',
                    'req' => $request->all()
                ], 422);
            }
        } catch (JWTException $e) {
            return response()->json([
              'success' => false,
              'message' => 'Silahkan Ulangi, Gagal Create Token',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login Sukses',
            'token' => $token,
        ]);

    }

    public function registerx(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user','token'),201);
    }

    public function Logout(){

        return response()->json([
          'success' => true,
          'message' => 'Sukses',
        ], 200);

    }

    static function GetAllUsers($req){
        $success = false; $message = '';
        $otoritas = HA::HakAksesUser(Auth::ID(),1);
        $data  = [];
        if($otoritas['lihat']){
            $data  = DB::table('users')->where('slug',0)->get();
            foreach($data as $dat){
                $slug  =  bin2hex(random_bytes(5));
                DB::table('users')->where('id',$dat->id)->update([
                    'slug'  => $slug,
                ]);
            }
            $query  = DB::table('users')->orderby('id','desc');
            if($req->search){
                $query->where('name','LIKE','%'.$req->search.'%');
            }
            $data  = $query->paginate(10);
            $success = true;
        }

        return [
          'success' => $success,
          'message' => $message,
          'data'  => $data,
          'ha'  => $otoritas,
        ];
    }

    static function GetBySlug($slug){
        $success = false;  $message = ''; $skpd = '';
        $data  = DB::table('users')->where('slug',$slug)->first();
        if($data){
            $success = true;
            $skpd  = DB::table('t_skpd')->where('id',$data->id_skpd)->value('nama_skpd');
        }

        return [
          'success' => $success,
          'message' => $message,
          'data'  => $data,
          'skpd'  => $skpd,
        ];
    }

    static function TambahDataUser($req){
        $success = false;  $message = 'Gagal Tambah Data User';
        // $data  = DB::table('users')->where('slug',$slug)->first();
        // if($data){
        //     $success = true;
        // }

        return response()->json([
          'username'=>['Username Sudah Digunakan, Silahkan Gunakan Yang Lain.'],
          'nip'=>['Data Sudah Teregister.'],
          // 'hp'=>['Nama Sudah Digunakan, Silahkan Gunakan Yang Lain.'],
          'req' => $req->all()
        ], 422);

        return response()->json([
            'success' => $success,
            'message' => $message,
            'req' => $req->all()
        ], 200);
    }

    static function UpdateDataUser($req){
        $success = false;  $message = 'Gagal Update Data User';
        $dat  = DB::table('users')->where('id',$req->id_user)->where('nip',$req->nip)->first();
        if($dat){
            $success = true;
            DB::table('users')->where('id',$dat->id)->update([
                'id_skpd' => $req->id_skpd,
            ]);
        }else{
            return response()->json([
              'skpd'=>['Data User Tidak Ditemukan.'],
            ], 422);
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'req' => $req->all()
        ], 200);
    }

    static function FindUsers($req){
        $success = false;  $message = 'Gagal Request Data';

        if(strlen($req->search) > 3){
            $data   = DB::table('users')->orWhere('name','LIKE','%'.$req->search.'%')
                      ->orWhere('nip','LIKE','%'.$req->search.'%')
                      ->orWhere('hp','LIKE','%'.$req->search.'%')->get();
            if(sizeOf($data)){
                foreach($data as $dat){
                    $datas[]  = [
                      'id'  => $dat->id,
                      'slug'  => $dat->slug,
                      'name'  => $dat->name,
                      'nip' => $dat->nip,
                    ];
                }
                $success = true;
            }else{
                $datas  = [];
            }

        }else{
            $datas  = [];
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $datas,
        ], 200);
    }

    public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        return response()->json(compact('user'));
    }
}
