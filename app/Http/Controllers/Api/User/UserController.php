<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseApiController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\Notification;
use Log;

class UserController extends BaseApiController
{


    public function wallets(){
        $wallets = auth()->user()->wallets;

        $response['response']['status'] = true;
        $response['response']['responseCode'] = 200;
        $response['response']['responseDescription'] = "Your Wallets.";
        $response['wallets'] = $wallets;

        return $this->respond($response);
    }

    public function userInfo(){
        $user = auth()->user();

        $user['wallets'] = Wallets::where('user_id', $user->id)->latest()->get();
        $user['transactions'] = WalletTransaction::where('user_id', $user->id)->latest()->get();

        $response['response']['status'] = true;
        $response['response']['responseCode'] = 200;
        $response['response']['responseDescription'] = "Your Information.";
        $response['user'] = $user;

        return $this->respond($response);
    }

    public function updateProfile(UpdateProfileRequest $request){
        $data = request()->except(['email']);

        auth()->user()->update($data);

        $user = auth()->user();


        $response['response']['status'] = true;
        $response['response']['responseCode'] = 200;
        $response['response']['responseDescription'] = "Profile updated succesffully.";
        $response['user'] = $user;

    }


}
