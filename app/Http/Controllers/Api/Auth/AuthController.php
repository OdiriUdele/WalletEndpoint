<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Services\User\UserService;
use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Wallet;
use Exception;
use Error;
use JWTAuth;

class AuthController extends BaseApiController
{
    protected $userservice;
    public function __construct(UserService $userservice){

        $this->userservice = $userservice;
        
    }

    public function register(RegisterRequest $request){//register new user
        $credentials = request(['email', 'password']);
        try{
            
            $request["password"] = bcrypt($request->password);

            $user = $this->userservice->storeUser($request); //store user information

            $token = JWTAuth::attempt($credentials); //get authentication token

            $response['response']['status'] = true;
            $response['response']['responseCode'] = 201;
            $response['response']['responseDescription'] = "Account created succesfully.";
            $response['token'] = $token;
            $response['user'] = $user;

            return $this->respondCreated($response,"User Signup succesfull.");
            
        }catch(\Error $e){
             return $this->respondWithError( "Something went wrong.");

        } catch(\Exception $e){
            return $this->respondWithError( "Something went wrong.");
            
        } catch (\JWTException $e) {

            return $this->respondWithError( "Could Not create Token.");
        }        
    }

    public function login(LoginRequest $request){

        $credentials = request(['email', 'password']);
        try{
            if (! $token = JWTAuth::attempt($credentials)) {//check user credentials

                return $this->respondWithError( "Invalid login details supplied.",401);
            }

            $user = User::where('email',$request->email)->first(); //fetch user
            
            $user['wallets'] = Wallet::where('user_id', $user->id)->latest()->get();//add user wallets
            
            $response['response']['status'] = true;
            $response['response']['responseCode'] = 200;
            $response['response']['responseDescription'] = "Login Successful";
            $response['token'] = $token;
            $response['user'] = $user;

            return $this->respond($response);

        }catch(\Exception $e){

              return $this->respondWithError( "Something went wrong.");

        }catch (\JWTException $e) {

            return $this->respondWithError( "Could not create token.");

        }
    }

    //user logout
    public function logout() {

        Auth::guard('api')->logout(); //logout user
    
        $response['response']['status'] = true;
        $response['response']['responseCode'] = 200;
        $response['response']['responseDescription'] = "Logged Outut Successfully.";

        return $this->respond($response);
    }

    
}
