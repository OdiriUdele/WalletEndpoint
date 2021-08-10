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



Route::prefix('v1')->namespace('Api')->middleware(['return-json'])->group(function(){

    //GENERAL OPERATIONS
    Route::prefix('/general')->group(function(){
        Route::get('/wallet-types', 'GeneralApiController@getWalletTypes');//fetch wallet types
        Route::get('/users', 'GeneralApiController@getAllUsers');//fetch all users
        Route::get('/users/{user}', 'GeneralApiController@getUserDetail');//fetches a user with associatd information
        Route::get('/wallets', 'GeneralApiController@getAllWallets');//get all wallets
        Route::get('/wallets/{wallet}', 'GeneralApiController@getWalletDetail');//get wallet with associated information
        Route::get('/detail-count', 'GeneralApiController@getDetailsCount');//get user copunt, wallet count, total wallet balance, transaction volume

        //SEND MONEY
        Route::post('/send-money', 'WalletController@creditWallet');
    });

    
    
    //STATE OPERATIONS
    Route::prefix('/state')->group(function(){
        Route::post('/import', 'GeneralApiController@ImportStateLga'); //import states and lga from excel file.
        Route::get('/all', 'GeneralApiController@fetchStateLga');// return all states with respective local government.
    });

    //AUTHENTICATION OPERATIONS
    Route::namespace('Auth')->group(function () {
        Route::post('/signup','AuthController@register');//register new user
        Route::post('/signin','AuthController@login');//login user
        Route::post('/logout','AuthController@logout')->middleware(['jwt.verify','jwt.auth']);//logout user

        //PASSWORD RESET OPERATIONS
        Route::prefix('password')->group(function(){
           Route::post('/token','ForgotPasswordController@setToken');// (1) get password reset token
           Route::get('/verify-token/{token}','ResetPasswordController@verify');//(2) verify password reset token
           Route::post('/reset','ResetPasswordController@reset');//(3) reset password after verification
        });
    });

    //USER OPERATIONS
    Route::namespace('User')->middleware(['return-json','jwt.verify','jwt.auth'])->group(function () {

        Route::prefix('user')->group(function(){

            Route::get('/', 'UserController@userInfo'); //fetch logged in user info
            Route::post('/update', 'UserController@updateProfile'); //update user info

            Route::prefix('/wallets')->group(function(){
                Route::get('/', 'UserController@wallets');//fetch logged in user wallets
                Route::get('/{wallet}', 'UserWalletController@viewSingleUserWallet');//fetch a specific wallet belonging to logged in user.
                Route::post('/create', 'UserWalletController@createWallet');//create new wallet
                Route::post('/{wallet}/update', 'UserWalletController@updateWallet');//update wallet
                Route::delete('/{wallet}/delete', 'UserWalletController@deleteWallet');//delete wallet
            });
        });

       
    });   

  
});
