<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use App\Http\Requests\Api\WalletRequest;
use App\Http\Requests\Api\UpdateWalletRequest;
use App\Http\Resources\Api\WalletResource;
use App\WalletTransaction;
use App\Wallet;
use Illuminate\Validation\Rule;

class UserWalletController extends BaseApiController
{
    public function viewSingleUserWallet(Wallet $wallet){
        try{
            //return single wallet
            $wallet =  new WalletResource($wallet);

            $response['response']['status'] = true;
            $response['response']['responseCode'] = 200;
            $response['response']['responseDescription'] = "Here is your wallet";
            $response['wallet'] = $wallet;

            return $this->respond($response);
            
        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage());
        }catch(\Error $e){
            return $this->respondWithError($e->getMessage());
        }
    }

    public function createWallet(WalletRequest $request){
        try{
            //create user wallet
            $user = auth()->user();
            $request['user_id'] = $user->id;
            $wallet = Wallet::create($request->all());

            $response['response']['status'] = true;
            $response['response']['responseCode'] = 201;
            $response['response']['responseDescription'] = "New Wallet Created";
            $response['data'] = $wallet;

            return $this->respondCreated($response, "Wallet Created Successfully");

        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage());
        }catch(\Error $e){
            return $this->respondWithError($e->getMessage());
        }
    }

    public function updateWallet(UpdateWalletRequest $request, Wallet $wallet){
        try{
            $input = $request->all();

            if($wallet->wallet_type_id !== $request->wallet_type_id){
                $this->validate($request, [
                    'wallet_type_id' => [
                        Rule::unique('wallets')->where(function ($query) {
                                return $query->where('user_id', $wallet->user_id);
                            })
                    ]
                ]); 
            }
            
            $update = $wallet->update($input);//update Wallet

            $wallet = $wallet->refresh();


            $response['response']['status'] = true;
            $response['response']['responseCode'] = 200;
            $response['response']['responseDescription'] = "Wallet Updated Successfully";
            $response['updated_wallet'] = $wallet;

            return $this->respond($response);

        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage());
        }catch(\Error $e){
            return $this->respondWithError($e->getMessage());
        }
    }

    public function deleteWallet(Wallet $wallet){
        try{
            if($wallet->delete()){

                $response['response']['status'] = true;
                $response['response']['responseCode'] = 200;
                $response['response']['responseDescription'] = "Wallet Deleted Successfully";

                return $this->respond($response);

            }else{
                return $this->respondWithError("Wallet Delete Failed");
            }

        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage());
        }catch(\Error $e){
            return $this->respondWithError($e->getMessage());
        }
    }
}
