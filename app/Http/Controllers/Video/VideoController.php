<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Video;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use File;

class VideoController extends Controller
{
    //
    // index news
    public function index(Request $request)
    {
        $search_query = $request->searchTerm;
        $news = Video::where([
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
        $news = Video::paginate($per_page)
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
        $per_page = isset($_GET['per_page']) ? $_GET['per_page'] : 4 ;
        $news = Video::orderBy('created_at', 'desc')
            ->paginate($per_page)
            ->getCollection()
            ->transform(function ($value) {
                $value->timeStamp();
                $value->user;
                return $value;
            });
        return response()->json($news);
    }

    static function GetData(){
        $per_page = isset($_GET['per_page']) ? $_GET['per_page'] : 4 ;
        $video = Video::orderBy('created_at', 'desc')
            ->paginate($per_page)
            ->getCollection()
            ->transform(function ($value) {
                $value->timeStamp();
                $value->user;
                return $value;
            });
        return response()->json([
          'success' => true,
          'message' => 'Sukses',
          'data'  => $video
        ]);

    }

    // detail news
    static function show($slug)
    {
        $news = Video::where('slug', $slug)->first();
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

    static function GetById($slug){
        $success = false; $message = 'Gagal';
        $exp   = explode('-',$slug);
        $video = Video::where('id', $exp[0])->first();
        if($video){
          $video->timeStamp();
          $video->user;
          $success = true; $message = 'Sukses';
        }
        return response()->json([
            'success'  => $success,
            'message' => $message,
            'data'  => $video
        ]);
    }

    static function UpdateData($slug,$req){
        $success = false; $message = 'Gagal';
        $exp   = explode('-',$slug);
        $video = Video::where('id', $exp[0])->first();
        if($video){
          $video->keterangan  = $req->keterangan;
          $video->status = $req->status;
          $video->update();
          $success = true; $message = 'Sukses';
        }
        return response()->json([
            'success'  => $success,
            'message' => $message,
            'data'  => $video
        ]);
    }
    // create new news
    static function store($request)
    {
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                'keterangan' => 'required|min:200',
                'file' => 'required|mimes:jpeg,jpg,png|max:5000',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Gagal Simpan Data', 'error' => $validator->errors()]);
            }

            $path  = 'file/'.date('Y').'/'.date('m').'/';
            $input['nm_file'] = $path.time() . '.' . $request->file('file')->getClientOriginalExtension();
            $input['id_user'] = Auth::id();
            // $input['slug'] = bin2hex(random_bytes(5));
            $input['mime'] = File::mimeType($request->file);

            $news = Video::create($input);
            if ($news) {
                $request->file->storeAs($path, time().'.'.$request->file('file')->getClientOriginalExtension() );
                return response()->json(['status' => 'success', 'message' => 'News created successfully']);
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
                $news = Video::where('slug', $slug)->first();
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
            $news = Video::where('slug', $slug)->first();

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
        $news = Video::paginate($per_page)
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
        $news = Video::paginate($per_page)
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
