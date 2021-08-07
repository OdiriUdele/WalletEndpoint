<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Http\Requests\Api\ResetRequest;
use App\PasswordReset;
use App\User;
use ErrorException;
use Exception;
use App\Services\User\UserService;

class ResetPasswordController extends BaseApiController
{
    protected $userservice;
    
    public function __construct(UserService $userservice){

        $this->userservice = $userservice;
        
    }

    public function verify(){

        if(!$this->userservice->TokenExpired(request()->token)){//CHECK TOKEN IS VALID

            return $this->respondWithError( "Token expired.",422);

        }

        $response['response']['status'] = true;
        $response['response']['responseCode'] = 200;
        $response['response']['responseDescription'] = "Token verified successfully.";

        return $this->respond($response);
    }

    
    public function reset(ResetRequest $request){//RESET PASSWORD
        
        try{

            $password = bcrypt($request->password);
        
            $token = PasswordReset::where('email',$request->email)->firstOrFail()->delete(); //delete password reset detail
           
            $user = User::where('email',$request->email)->firstOrFail()->update(['password' => $password]);

            $response['response']['status'] = true;
            $response['response']['responseCode'] = 200;
            $response['response']['responseDescription'] = "Password reset successfully.";
    
            return $this->respond($response);

        }catch(Exception $e){

            return $this->respondWithError( "Something went wrong.",500);
        }catch (ErrorException $e) {
            return $this->respondWithError( "Specified email not found.",500);

        }
        
    }

    
}
