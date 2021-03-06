<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use App\Http\Resources\Api\WalletResource;
use App\Http\Resources\Api\StateResource;
use App\WalletTransaction;
use App\Wallet;
use App\WalletType;
use App\State;
use App\User;

use App\Imports\StateLgaImport;
use Exception;
use Excel;

class GeneralApiController extends BaseApiController
{

    public function getAllUsers(){//GET ALL USERS
        try{
            //return all users
            $users =  User::paginate(10);

            $response['response']['status'] = true;
            $response['response']['responseCode'] = 200;
            $response['response']['responseDescription'] = "All Users";
            $response['users'] = $users;

            return $this->respond($response);
            
        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage());
        }catch(\Error $e){
            return $this->respondWithError($e->getMessage());
        }
    }

    public function getWalletTypes(){//GET ALL WALLET TYPES
        try{
            //return all wallet types
            $types =  WalletType::paginate(10);

            $response['response']['status'] = true;
            $response['response']['responseCode'] = 200;
            $response['response']['responseDescription'] = "Wallet Types";
            $response['wallet_types'] = $types;

            return $this->respond($response);
            
        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage());
        }catch(\Error $e){
            return $this->respondWithError($e->getMessage());
        }
    }

    public function getAllWallets(){//GET ALL WALLETS
        try{
            //return all wallets
            $users =  Wallet::paginate(10);

            $response['response']['status'] = true;
            $response['response']['responseCode'] = 200;
            $response['response']['responseDescription'] = "All Wallets";
            $response['wallets'] = $users;

            return $this->respond($response);
            
        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage());
        }catch(\Error $e){
            return $this->respondWithError($e->getMessage());
        }
    }

    public function getUserDetail(User $user){//GET USER DETAILS
        try{
            
            $user_wallets = $user->wallets;//return user wallets
            $user_transactions = $user->transactions;//return user transactions

            $response['response']['status'] = true;
            $response['response']['responseCode'] = 200;
            $response['response']['responseDescription'] = "User Information";
            $response['user'] = $user;
            $response['user_wallets'] = $user->wallets;
            $response['user_transactions'] = $user_transactions;

            return $this->respond($response);

        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage());
        }catch(\Error $e){
            return $this->respondWithError($e->getMessage());
        }
    }

    public function getWalletDetail(Wallet $wallet){//GET WALLET DETAILS
        try{
            
            $wallet_owner = $wallet->user;//return wallet owner
            $wallet_transactions = $wallet->transactions;//return wallet transactions

            $response['response']['status'] = true;
            $response['response']['responseCode'] = 200;
            $response['response']['responseDescription'] = "Wallet Information";
            $response['Wallet'] = $wallet;
            $response['Owner'] = $wallet_owner;
            $response['Wallet_transactions'] = $wallet_transactions;

            return $this->respond($response);

        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage());
        }catch(\Error $e){
            return $this->respondWithError($e->getMessage());
        }
    }


    public function getDetailsCount(){ //Get cdetails count information
        try{
            
            $user_count = User::count();//users count
            $wallet_count = Wallet::count();// wallets count()
            $total_wallet_balance = Wallet::sum('balance');//total wallet balance

            $debit_transaction = WalletTransaction::where('type','DEBIT')->sum('amount');
            $credit_transaction = WalletTransaction::where('type','<>','DEBIT')->sum('amount');

            //transactions_volume = credi - debit
            $transaction_volume = $credit_transaction - $debit_transaction;

            $data= [
                'user_count' => $user_count,
                'wallet_count'=>$wallet_count,
                'total_wallet_balance'=>$total_wallet_balance,
                'transaction_volume'=>$transaction_volume
            ];

            $response['response']['status'] = true;
            $response['response']['responseCode'] = 200;
            $response['response']['responseDescription'] = "Wallet Information";
            $response['data'] = $data;

            return $this->respond($response);

        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage());
        }catch(\Error $e){
            return $this->respondWithError($e->getMessage());
        }
    }


    public function ImportStateLga(Request $request){
        $this->validate($request, [
            'state_lga' => 'required|file|mimes:xls,xlsx'
        ]);

        try{

             Excel::import(new StateLgaImport,$request->file('state_lga'));

            $response['response']['status'] = true;
            $response['response']['responseCode'] = 200;
            $response['response']['responseDescription'] = "Import Successful";

            return $this->respond($response);

        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage());
        }catch(\Error $e){
            return $this->respondWithError($e->getMessage());
        }
    }


    public function fetchStateLga(){

        try{

            $states = State::all();

            $response['response']['status'] = 'success';
            $response['response']['responseCode'] = 200;
            $response['response']['responseDescription'] = "States and LGAs";
            $response['state']= StateResource::collection($states);

            return $this->respond($response);

        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage());
        }catch(\Error $e){
            return $this->respondWithError($e->getMessage());
        }
    }
        
    

}
