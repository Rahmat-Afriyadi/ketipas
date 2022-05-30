<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Berita;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use File;
use App\Http\Controllers\Admin\HAController as HA;

class NewsController extends Controller
{

    public function index(Request $request)
    {
        $search_query = $request->searchTerm;
        $news = Berita::where([
            ['judul', 'LIKE', '%' . $search_query . '%'],
        ])->get()
            ->transform(function ($value) {
                $value->timeStamp();
                $value->user;
                return $value;
            });
        return response()->json($news);
    }

    public function paginateIndex(Request $request)
    {
        $per_page = isset($_GET['per_page']) ? $_GET['per_page'] : 4;
        $news = Berita::paginate($per_page)
            ->getCollection()
            ->transform(function ($value) {
                $value->timeStamp();
                $value->user;
                return $value;
            });
        return view('excell', ['posts' => $news]);
        // return response()->json($news);
    }

    public function last_post()
    {
        $per_page = isset($_GET['per_page']) ? $_GET['per_page'] : 50 ;

        $ha = HA::GetOtoritas(Auth::id(),4);

        if($ha['tambah']){
            $news = Berita::orderBy('created_at', 'desc')
                ->paginate($per_page)
                ->getCollection()
                ->transform(function ($value) {
                    $value->timeStamp();
                    $value->user;
                    return $value;
                });

        }else{
            $news = Berita::orderBy('created_at', 'desc')
                ->where('id_user',Auth::id())
                ->paginate($per_page)
                ->getCollection()
                ->transform(function ($value) {
                    $value->timeStamp();
                    $value->user;
                    return $value;
                });
        }
        return response()->json([
            'success'  => true,
            'message' => 'Sukses',
            'data'  => $news,
            'ha'  => $ha
        ]);
    }

    static function WebLastPost(){
        $per_page = isset($_GET['per_page']) ? $_GET['per_page'] : 50;
        $news = Berita::orderBy('created_at', 'desc')
            ->where('status',1)
            ->paginate($per_page)
            ->getCollection()
            ->transform(function ($value) {
                $value->timeStamp();
                $value->user;
                return $value;
            });
        return response()->json([
            'success'  => true,
            'message' => 'Sukses',
            'data'  => $news,
        ]);
    }

    // detail news
    static function show($slug)
    {
        $news = Berita::where('slug', $slug)->first();
        if($news){
          $news->timeStamp();
          $news->user;
          return response()->json([
              'status'  => true,
              'message' => 'Data Ditemukan',
              'data'  => $news
          ]);
        }
        return response()->json([
            'status'  => false,
            'message' => 'Data Tidak Ditemukan',
            'slug'  => $slug
        ]);
    }

    static function GetById($url){
        $exp  = explode('-',$url);
        $news = Berita::where('id',$exp[0])->first();
        if($news){
          $news->timeStamp();
          $news->user;
          return response()->json([
              'success'  => true,
              'message' => 'Data Ditemukan',
              'data'  => $news
          ]);
        }
        return response()->json([
            'success'  => false,
            'message' => 'Data Tidak Ditemukan',
            'id'  => $exp[0]
        ]);
    }

    static function UpdateBerita($url,$req){
        $ha = HA::GetOtoritas(Auth::id(),4);
        if($ha['edit']){
            $status = $req->status;
        }else{
            $status = 0;
        }
        $success = false; $message = 'Gagal Update';
        $input = $req->all();
        $validator = Validator::make($input, [
            'judul' => 'required|min:6|max:100',
            'uraian' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'state' => '100', 'message' => $validator->errors()]);
        }
        $exp  = explode('-',$url);
        $news = Berita::where('id',$exp[0])->first();
        if($news){
          $news->judul = $req->judul;
          $news->uraian = $req->uraian;
          $news->status = $status;
          $news->update();
          $success = true; $message = 'Sukses Update';
        }
        return response()->json([
            'success'  => $success,
            'message' => $message,
            'data'  => $news
        ]);


    }
    // create new news
    static function store($request)
    {
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                'judul' => 'required|min:6|max:100|unique:ta_berita,judul',
                'uraian' => 'required',
                'file' => 'required|mimes:jpeg,jpg,png|max:5000',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'state' => '100', 'message' => $validator->errors()]);
            }

            $path  = date('Y').'/'.date('m').'/';
            $input['filepath'] = $path.time() . '.' . $request->file('file')->getClientOriginalExtension();
            $input['id_user'] = Auth::id();
            $input['mime'] = File::mimeType($request->file);

            $news = Berita::create($input);
            if ($news) {
                $request->file->storeAs('public/'.$path, time().'.'.$request->file('file')->getClientOriginalExtension() );
                return response()->json(['success' => true, 'message' => 'Tambah Data Berita Berhasil']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $slug)
    {
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                'judul' => 'required|min:6|max:50|regex:/^[\s\w-]*$/|unique:posts,judul,' . $input['judul'] . ',judul',
                'isi' => 'required',
                'thumbnail' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'Failed', 'state' => '100', 'message' => $validator->errors()]);
            }
            // validate old image
            try {
                $news = Berita::where('slug', $slug)->first();
                $image_before = $news->thumbnail;
                if ($image_before !== $request->thumbnail) {
                    $validator = Validator::make($request->all(), [
                        'thumbnail' => 'required|mimes:jpeg,jpg,png|max:5000',
                    ]);
                    if ($validator->fails()) {
                        return response()->json(['status' => 'Failed', 'state' => '100', 'message' => $validator->errors()]);
                    }
                }
            } catch (\Throwable $th) {
                return response()->json(['status' => 'error', 'message' => 'undefined']);
            }
            if ($image_before !== $request->thumbnail) {
                $input['thumbnail'] = time() . '-' . $request->file('thumbnail')->getClientOriginalName();
                $request->thumbnail->storeAs('images/news_thumb', $input['thumbnail']);
            }
            // updating news
            $news->update($input);
            if ($news) {
                $image_before !== $request->thumbnail
                    ? Storage::delete('images/news_thumb/' . $image_before)
                    : 'no';
                return response()->json(['status' => 'success', 'message' => 'Update updated successfully']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function destroy($slug)
    {
        echo "test";
        try {
            $news = Berita::where('slug', $slug)->first();

            if ($news->delete()) {
                Storage::delete('images/news_thumb/' . $news->thumbnail);
                return response()->json(['status' => 'success', 'message' => 'News deleted successfully']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function downloadExcell()
    {

        $per_page = isset($_GET['per_page']) ? $_GET['per_page'] : 4;
        $news = Berita::paginate($per_page)
        ->getCollection()
        ->transform(function ($value) {
            $value->timeStamp();
            $value->user;
            return $value;
        });
        return view('excell', ['posts' => $news]);

    }

    public function downloadPDF()
    {

        $per_page = isset($_GET['per_page']) ? $_GET['per_page'] : 4;
        $news = Berita::paginate($per_page)
        ->getCollection()
        ->transform(function ($value) {
            $value->timeStamp();
            $value->user;
            return $value;
        });

        $pdf = PDF::setOptions(['defaultFont' => 'serif'])->loadView('pdf', ['posts' => $news]);
        return $pdf->stream('posts.pdf');
        // return view('pdf', ['posts' => $posts]);
    }
}
