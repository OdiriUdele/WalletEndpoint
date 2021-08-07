<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::prefix('v1')->group(function(){
    Route::prefix('/post')->namespace('Api')->middleware(['return-json'])->group(function(){
        Route::get('/', 'PostApiController@viewAllPost');
        Route::get('/{post}', 'PostApiController@viewSinglePost');
        Route::post('/create', 'PostApiController@createPost');
        Route::post('/{post}/update', 'PostApiController@updatePost');
        Route::delete('/{post}/delete', 'PostApiController@deletePost');
    });

    Route::namespace('Api\Auth')->middleware(['return-json'])->group(function () {
        Route::post('/signup','AuthController@register');
        Route::post('/signin','AuthController@login');
        Route::post('/logout','AuthController@logout')->middleware(['jwt.verify','jwt.auth']);
        Route::post('/get-reset-link','ForgotPasswordController@setToken');
        Route::get('/verify-token/{token}','ResetPasswordController@verify');
        Route::post('/reset-password','ResetPasswordController@reset');
        Route::get('/resend-email-verification/{email}','VerifyEmailController@resend');
        Route::get('/verify-email/{token}','VerifyEmailController@verifyEmail');
        Route::post('/get-token','VerifyEmailController@getToken');
    });

    Route::namespace('Api\User')->middleware(['return-json','jwt.verify','jwt.auth'])->group(function () {
        Route::get('/user', 'UserController@userInfo');
        Route::get('/users/wallets', 'UserController@wallets');
        Route::post('/users/update', 'UserController@updateProfile');
       
    });
});