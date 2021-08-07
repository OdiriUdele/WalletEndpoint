<?php
namespace App\Services\User;

use App\User;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\PasswordReset;
use App\Services\Service;
use Log;


class UserService extends Service{
    
    public function storeUser($request){
        
        $data = $this->build_user_data();

        $user = User::create($data);

        return $user;
    }



    public function build_user_data($staff = false){

        $data = request()->only(["first_name", "last_name", "password", "email"]);

        $data['email_token'] = $this->generate_random_token(80);
        return $data;
    }

    //check if a user exists with email address
    public function check_email($email){
        $email = User::whereEmail($email)->first();
        return $email;
    }




    public function generate_random_token(int $length){
        return Str::random($length);
    }

    /**
     * token related methods
     * 
     * **/

    public function getToken($email){
        $oldToken = PasswordReset::where('email', $email)->first();
        if($oldToken) {
          $now = Carbon::now();
          $oldToken->update(['created_at'=>$now]);
          \Log::info($oldToken->token);
          return $oldToken->token;
        }
        
        $token = $this->generate_random_token(80);

        $this->storeToken($email, $token);
        \Log::info($token);
        
        return $token;
    }


    private function storeToken($email, $token){
        $data = ['email' => $email, 'token' => $token];
        PasswordReset::create($data);
    }


    public function verifyUser($token){
        $token = User::where('email_token',$token)->first();
        return $token;
    }

    public function TokenExpired($reqtoken){

        $token = PasswordReset::where('token', $reqtoken)->first();
        if(!$token){
            return false;
        }
        if($token){
            $duration = 10;
            $timeinterval = date('Y-m-d H:i:s',strtotime($token->created_at->toDateTimeString()."+".$duration." minutes"));
            return (strtotime(date('Y-m-d H:i:s')) > strtotime($timeinterval)) ? false : true;
        }
       return true;
    }


}