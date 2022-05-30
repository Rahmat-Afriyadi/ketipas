<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\LoginController as Login;
use App\Http\Controllers\User\RouteController as UserRoute;
use App\Http\Controllers\News\RouteController as News;
use App\Http\Controllers\Referensi\RouteController as Referensi;
use App\Http\Controllers\PPDB\RouteController as PPDB;
use App\Http\Controllers\Site\RouteController as Site;
use App\Http\Controllers\Galeri\RouteController as Galeri;
use App\Http\Controllers\Video\RouteController as Video;
use App\Http\Controllers\Regulasi\RouteController as Regulasi;
use App\Http\Controllers\Admin\RouteController as Admin;
use App\Http\Controllers\Sekolah\RouteController as Sekolah;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('login', [Login::class, 'login']);
Route::post('logout', [Login::class, 'Logout']);

Route::group(['prefix'=>'user', 'middleware'=>'jwt.verify'], function() {
    Route::get('/',[UserRoute::class, 'index']);
    Route::get('/{satu}',[UserRoute::class, 'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[UserRoute::class, 'IndexRouteDua']);

    Route::post('/',[UserRoute::class, 'index']);
    Route::post('/{satu}',[UserRoute::class, 'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[UserRoute::class, 'IndexRouteDua']);

});

Route::group(['prefix'=>'berita', 'middleware'=>'jwt.verify'], function() {
    Route::get('/',[News::class, 'index']);
    Route::get('/{satu}',[News::class, 'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[News::class, 'IndexRouteDua']);

    Route::post('/',[News::class, 'index']);
    Route::post('/{satu}',[News::class, 'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[News::class, 'IndexRouteDua']);

});

Route::group(['prefix'=>'web/berita'], function() {
    Route::get('/',[News::class, 'index']);
    Route::get('/{satu}',[News::class, 'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[News::class, 'IndexRouteDua']);

    Route::post('/',[News::class, 'index']);
    Route::post('/{satu}',[News::class, 'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[News::class, 'IndexRouteDua']);

});

Route::group(['prefix'=>'site', 'middleware'=>'jwt.verify'], function() {
    Route::get('/',[Site::class, 'index']);
    Route::get('/{satu}',[Site::class, 'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[Site::class, 'IndexRouteDua']);

    Route::post('/',[Site::class, 'index']);
    Route::post('/{satu}',[Site::class, 'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[Site::class, 'IndexRouteDua']);

});

Route::group(['prefix'=>'galeri', 'middleware'=>'jwt.verify'], function() {
    Route::get('/',[Galeri::class, 'index']);
    Route::get('/{satu}',[Galeri::class, 'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[Galeri::class, 'IndexRouteDua']);

    Route::post('/',[Galeri::class, 'index']);
    Route::post('/{satu}',[Galeri::class, 'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[Galeri::class, 'IndexRouteDua']);

});

Route::group(['prefix'=>'web/galeri'], function() {
    Route::get('/',[Galeri::class, 'index']);
    Route::get('/{satu}',[Galeri::class, 'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[Galeri::class, 'IndexRouteDua']);

    Route::post('/',[Galeri::class, 'index']);
    Route::post('/{satu}',[Galeri::class, 'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[Galeri::class, 'IndexRouteDua']);

});

Route::group(['prefix'=>'video', 'middleware'=>'jwt.verify'], function() {
    Route::get('/',[Video::class, 'index']);
    Route::get('/{satu}',[Video::class, 'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[Video::class, 'IndexRouteDua']);

    Route::post('/',[Video::class, 'index']);
    Route::post('/{satu}',[Video::class, 'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[Video::class, 'IndexRouteDua']);

});

Route::group(['prefix'=>'web/video'], function() {
    Route::get('/',[Video::class, 'index']);
    Route::get('/{satu}',[Video::class, 'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[Video::class, 'IndexRouteDua']);

    Route::post('/',[Video::class, 'index']);
    Route::post('/{satu}',[Video::class, 'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[Video::class, 'IndexRouteDua']);

});

Route::group(['prefix'=>'regulasi', 'middleware'=>'jwt.verify'], function() {
    Route::get('/',[Regulasi::class, 'index']);
    Route::get('/{satu}',[Regulasi::class, 'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[Regulasi::class, 'IndexRouteDua']);

    Route::post('/',[Regulasi::class, 'index']);
    Route::post('/{satu}',[Regulasi::class, 'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[Regulasi::class, 'IndexRouteDua']);

});

Route::group(['prefix'=>'web/regulasi'], function() {
    Route::get('/',[Regulasi::class, 'index']);
    Route::get('/{satu}',[Regulasi::class, 'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[Regulasi::class, 'IndexRouteDua']);

    Route::post('/',[Regulasi::class, 'index']);
    Route::post('/{satu}',[Regulasi::class, 'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[Regulasi::class, 'IndexRouteDua']);

});

// Route::middleware(['auth:api','verified'])->group(function () {
//     Route::controller(PostController::class)->group(function () {
//         Route::post('/news', 'store')->name('news_store');
//         Route::put('/news/update/{slug}', 'update')->name('news_update');
//         Route::delete('/news/destroy/{slug}', 'destroy')->name('news_delete');
//     });
// });

Route::group(['prefix'=>'administrator', 'middleware'=>'jwt.verify'], function() {
    Route::get('/',[Admin::class, 'index']);
    Route::get('/{satu}',[Admin::class, 'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[Admin::class, 'IndexRouteDua']);
    Route::get('/{satu}/{dua}/{tiga}',[Admin::class, 'IndexRouteTiga']);

    Route::post('/',[Admin::class, 'index']);
    Route::post('/{satu}',[Admin::class, 'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[Admin::class, 'IndexRouteDua']);
    Route::post('/{satu}/{dua}/{tiga}',[Admin::class, 'IndexRouteTiga']);

});

Route::group(['prefix'=>'sekolah', 'middleware'=>'jwt.verify'], function() {
    Route::get('/',[Sekolah::class, 'index']);
    Route::get('/{satu}',[Sekolah::class, 'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[Sekolah::class, 'IndexRouteDua']);
    Route::get('/{satu}/{dua}/{tiga}',[Sekolah::class, 'IndexRouteTiga']);

    Route::post('/',[Sekolah::class, 'index']);
    Route::post('/{satu}',[Sekolah::class, 'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[Sekolah::class, 'IndexRouteDua']);
    Route::post('/{satu}/{dua}/{tiga}',[Sekolah::class, 'IndexRouteTiga']);

});

Route::group(['prefix'=>'ppdb', 'middleware'=>'jwt.verify'], function() {
    Route::get('/',[PPDB::class, 'index']);
    Route::get('/{satu}',[PPDB::class, 'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[PPDB::class, 'IndexRouteDua']);
    Route::get('/{satu}/{dua}/{tiga}',[PPDB::class, 'IndexRouteTiga']);

    Route::post('/',[PPDB::class, 'index']);
    Route::post('/{satu}',[PPDB::class, 'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[PPDB::class, 'IndexRouteDua']);
    Route::post('/{satu}/{dua}/{tiga}',[PPDB::class, 'IndexRouteTiga']);

});

Route::group(['prefix'=>'web/ppdb'], function() {
    Route::get('/',[PPDB::class, 'index']);
    Route::get('/{satu}',[PPDB::class, 'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[PPDB::class, 'IndexRouteDua']);

    Route::post('/',[PPDB::class, 'index']);
    Route::post('/{satu}',[PPDB::class, 'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[PPDB::class, 'IndexRouteDua']);

});

Route::group(['prefix'=>'referensi'], function() {
    Route::get('/',[Referensi::class, 'index']);
    Route::get('/{satu}',[Referensi::class, 'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[Referensi::class, 'IndexRouteDua']);

    Route::post('/',[Referensi::class, 'index']);
    Route::post('/{satu}',[Referensi::class, 'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[Referensi::class, 'IndexRouteDua']);

});
