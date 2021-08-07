<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use App\Services\Service;
use App\Http\Requests\Api\SendMoneyRequest;
use App\WalletTransaction;
use App\Wallet;

class WalletController extends BaseApiController
{
    protected $service;
    public function __construct(Service $service){

        $this->service = $service;
        
    }

    public function creditWallet(SendMoneyRequest $request){
        $res =  DB::transaction(function () use ($loan_modification) {

            $request['sender_receiver_wallet_id']=$request->receiver_wallet;

            if(!$this->service->check_wallet_balance($request->wallet_id, $request->amount)){
                return [false, 'Wallet minimum balance surpassed'];
            }

            if(!$this->service->creditWallet($request->receiver_wallet,$request->wallet_id,$request->amount)){
                return [false, 'Something went wrong'];
            }

            if(!$this->service->debitWallet($request->wallet_id,$request->receiver_wallet,$request->amount)){
                return [false, 'Something went wrong'];
            }

            return [true, 'Wallet Credited Successfully'];


        });
    }

}