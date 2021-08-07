<?php
namespace App\Services;

use App\Wallet;
use App\WalletTransaction;
use App\User;
use Mail;
use Log;

use Illuminate\Support\Str;
use Carbon\Carbon;
use DB;

class Service{

    public function check_wallet_balance($wallet_id, $amount){

        $wallet = Wallet::where('id',$wallet_id)->first();

        $minimum_balance = $wallet->type ? $wallet->type->minimum_balance : 0;//get wallet_type minimum balance

        $wallet_remaining_balance = $wallet->balance - +$amount;

        if($wallet_remaining_balance < $minimum_balance){
            return false;
        }

        return true;


    }

    public function creditWallet($wallet_id, $sender_receiver_wallet_id, $amount, $narration){
       
            try{

                $wallet = Wallet::where('id',$wallet_id)->first();

                $credit = [
                    'user_id'=> $wallet->user->id,
                    'wallet_id'=> $wallet_id,
                    'sender_receiver_wallet_id'=>$sender_receiver_wallet_id,
                    'amount'=>$amount,
                    'type'=>'CREDIT',
                    'narration'=>$narration
                ];

                $wallet->balance += $amount;
                $wallet->save();

                $transaction = WalletTransaction::insert($credit);

                return $transaction;

            }catch(\Exception $e){
                return false;
            }catch(\Error $e){
                return false;
            }
    }

    public function debitWallet($wallet_id, $sender_receiver_wallet_id, $amount, $narration){
        
            try{
                $wallet = Wallet::where('id',$wallet_id)->first();

                $credit = [
                    'user_id'=> $wallet->user->id,
                    'wallet_id'=> $wallet_id,
                    'sender_receiver_wallet_id'=>$sender_receiver_wallet_id,
                    'amount'=>$amount,
                    'type'=>'DEBIT',
                    'narration'=>$narration
                ];

                $wallet->balance -= $amount;
                $wallet->save();

                $transaction = WalletTransaction::insert($credit);

                return $transaction;

            }catch(\Exception $e){
                return false;
            }catch(\Error $e){
                return false;
            }
        
    }

    public function depositWallet($wallet_id, $amount, $narration){

           try{
                $wallet = Wallet::where('id',$wallet_id)->first();

                $credit = [
                    'user_id'=> $wallet->user->id,
                    'wallet_id'=> $wallet_id,
                    'sender_receiver_id'=>$wallet_id,
                    'amount'=>$amount,
                    'type'=>'DEPOSIT',
                    'narration'=>$narration
                ];

                $wallet->balance += $amount;
                $wallet->save();

                $transaction = WalletTransaction::insert($credit);

                return $transaction;

            }catch(\Exception $e){
                return false;
            }catch(\Error $e){
                return false;
            }

        
    }
}