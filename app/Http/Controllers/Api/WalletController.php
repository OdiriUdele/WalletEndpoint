<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use App\Services\Service;
use App\Http\Requests\Api\SendMoneyRequest;
use App\WalletTransaction;
use App\Wallet;
use DB;

class WalletController extends BaseApiController
{
    protected $service;
    public function __construct(Service $service){

        $this->service = $service;
        
    }

    public function creditWallet(SendMoneyRequest $request){

        DB::beginTransaction();

            try{

                $request['sender_receiver_wallet_id']=$request->receiver_wallet_id;
                $request['wallet_id']=$request->sender_wallet_id;

                $narration = $request->narration ? $request->narration : '';

                //NB: deposit; receiver wallet_id and receiver_sender_id are same.
                if($request->receiver_wallet_id ===  $request->sender_wallet_id){

                    if(!$this->service->depositWallet($request->sender_wallet_id,$request->amount,$narration)){
                        return [false, 'Something went wrong with making deposit to wallet.'];
                    }
                   
                }else{

                    //check balance
                    if(!$this->service->check_wallet_balance($request->sender_wallet_id, $request->amount)){
                        return $this->respondWithError('Wallet minimum balance surpassed.');
                    }


                    //credit receiverwallet
                    if(!$this->service->creditWallet($request->receiver_wallet_id,$request->sender_wallet_id,$request->amount,$narration)){
                        return $this->respondWithError('Something went wrong with crediting wallet.');
                    }

                    //debit sender
                    if(!$this->service->debitWallet($request->sender_wallet_id,$request->receiver_wallet_id,$request->amount,$narration)){
                        return $this->respondWithError('Something went wrong with debitting wallet.');
                    }
                }

                DB::commit();

                $wallet = Wallet::find($request->sender_wallet_id);

                $response['response']['status'] = true;
                $response['response']['responseCode'] = 200;
                $response['response']['responseDescription'] = "Operation was successful";
                $response['new_balance'] = number_format($wallet->balance,2);
                $response['wallet'] = $wallet;

                return $this->respond($response);

            }catch(\Exception $e){
                \Log::info($e);
                DB::rollback();
                return $this->respondWithError('Something Went Wrong.');
            }catch(\Error $e){
                \Log::info($e);
                DB::rollback();
                return $this->respondWithError('Something Went Wrong.');
            }

    
        
    }

}