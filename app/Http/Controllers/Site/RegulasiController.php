<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Regulasi;
use App\Models\User;
use File;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class RegulasiController extends Controller
{

    public function last_post()
    {
        $per_page = isset($_GET['per_page']) ? $_GET['per_page'] : 4 ;
        $news = Regulasi::orderBy('created_at', 'desc')
            ->paginate($per_page)
            ->getCollection()
            ->transform(function ($value) {
                $value->timeStamp();
                $value->user;
                return $value;
            });
        return response()->json([
          'success' => true,
          'message' => 'Sukses Get Data',
          'data'  => $news,
        ]);
    }

    // detail news
    static function show($slug)
    {
        $news = Regulasi::where('slug', $slug)->first();
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
    // create new news
    public function store(Request $request)
    {
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                'judul' => 'required|min:6|max:50|regex:/^[\s\w-]*$/|unique:ta_regulasi,judul',
                'keterangan' => 'required',
                'file' => 'required|mimes:pdf,PDF|max:5000',
            ]);
            if ($validator->fails()) {
                return response()->json([
                  'success' => false,
                  'message' => $validator->errors(),
                  'request' => $request->all()
                ]);
            }

            $path  = date('Y').'/'.date('m').'/';
            $input['nm_file'] = $path.time() . '.' . $request->file('file')->getClientOriginalExtension();
            $input['user_id'] = Auth::id();
            $input['tanggal'] = date('Y-m-d');
            $input['mime'] = File::mimeType($request->file);

            $news = Regulasi::create($input);
            if ($news) {
                $request->file->storeAs('public/'.$path, time().'.'.$request->file('file')->getClientOriginalExtension() );
                return response()->json(['success' => true, 'message' => 'News created successfully']);
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
                $news = Regulasi::where('slug', $slug)->first();
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
            $news = Regulasi::where('slug', $slug)->first();

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
        $news = Regulasi::paginate($per_page)
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
        $news = Regulasi::paginate($per_page)
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
