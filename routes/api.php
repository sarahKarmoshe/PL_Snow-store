<?php

use App\Http\Controllers\MediaController;
use App\Http\Controllers\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;

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
//
//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

//
//Route::get('/test-online', function () {
//    dd('i am online ^_^');
//});



Route::post('signup', [RegisterController::class, 'signUp']);
Route::post('login', [RegisterController::class, 'login']);


Route::prefix('products')->group(function () {

    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{product}', [ProductController::class, 'show']);
    Route::get('/{product}/comments',[CommentController::class,'index']);

});
Route::post('search', [ProductController::class, 'search']);

Route::middleware('auth:api')->group(function () {
    Route::get("email/verify", [RegisterController::class, "verify"])->name("verificationapi.verify");
Route::get("email/resend", [RegisterController::class,"resend"])->name("verificationapi.resend");


    Route::prefix('products')->group(function () {
        Route::post('/', [ProductController::class, 'store']);
        Route::post('update/{product}', [ProductController::class, 'update']);
        Route::get('delete/{product}', [ProductController::class, 'destroy']);
        Route::prefix('{product}/comments')->group(function () {
            Route::delete('/{comment}', [CommentController::class, 'destroy']);
            Route::post('/', [CommentController::class, 'store']);
        });
        Route::prefix('{product}/likes')->group(function () {
            Route::get('/', [LikeController::class, 'IsLiked']);
        });
    });

    Route::get('logout', [RegisterController::class, 'logout']);
    Route::get('myproducts',[RegisterController::class,'myProducts']);
    Route::get('myprofile',[RegisterController::class,'myProfile']);

});
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/', [CategoryController::class, 'store']);
});
