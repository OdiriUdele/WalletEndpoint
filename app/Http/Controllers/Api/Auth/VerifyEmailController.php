<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use App\Http\Requests\Api\VerifyEmailRequest;
use App\Http\Requests\Api\ResendEmailRequest;
use App\Services\User\UserService;
use App\User;
use Mail;
use Log;

class VerifyEmailController extends BaseApiController
{
    protected $userservice;
    
    public function __construct(UserService $userservice){

        $this->userservice = $userservice;
        
    }

    public function verifyEmail(){
        
        if(!$this->userservice->verifyUser(request()->token)){//CHECK IF USER WITH TOKEN EXISTS

            return $this->respondWithError( "Invalid token.",422);

        }
        
        $user = User::where('email_token',request()->token)->first();
        $verify =  $user->update(['active'=>1,'email_token'=>null, 'email_verified_at'=>now()]); //update user status
        $user =  $user->refresh();

        $response['response']['status'] = true;
        $response['response']['responseCode'] = 200;
        $response['response']['responseDescription'] = "Email verified succesfully.";
        $response['data'] = $user;

        return $this->respond($response);
    }

    public function resetToken(){

        if(!$this->userservice->check_email(request()->email)){//CHECK IF EMAIL IS VALID

            return $this->respondWithError( "Invalid credentials supplied.",422);

        }

        try{

            $token = $this->userservice->generate_random_token(80); //REGENRATE TOKEN

            User::whereEmail(request()->email)->first()->update(['email_token'=>$token]);//UPDATE USER TOKEN

            $response['response']['status'] = true;
            $response['response']['responseCode'] = 200;
            $response['response']['responseDescription'] = "Verification link sent successfully.";
            $response['data'] = $user;

            return $this->respond($response);

        }catch(\Exception $e){

            return $this->respondWithError( $e->getMessage(),500);

        }
    }
    
}
