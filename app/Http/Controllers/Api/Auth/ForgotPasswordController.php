<?php
namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\PasswordReset;
use App\Http\Requests\Api\ForgotPasswordRequest;
use Exception;
use App\Services\User\UserService;

class ForgotPasswordController extends BaseApiController
{
    protected $userservice;
    
    public function __construct(UserService $userservice){

        $this->userservice = $userservice;
        
    }

    public function setToken(ForgotPasswordRequest $request){
        
        if(!$this->userservice->check_email($request->email)){//confirm if user exists

            return $this->respondWithError( "We can't find a user with the supplied email id.",422);
        }
        try{
            
            $token = $this->userservice->getToken(request()->email); //call service to genrate token for user

            $response['response']['status'] = true;
            $response['response']['responseCode'] = 200;
            $response['response']['responseDescription'] = "Reset Token Generated successfully.";
            $response['reset_token'] = $token;

            return $this->respond($response);

        }catch(Exception $e){

            return $this->respondWithError( "Something went wrong.");
        }
    }


}

