<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        static::$wrap = "Wallet";
        
        return [
           'id' =>$this->id,
            'wallet_name'=>$this->wallet_name,
            'wallet_type'=>$this->type ? $this->type:'Not Found',
            'owner'=>$this->user ? $this->user:'Not Found',
            'transaction_history'=>$this->transactions ? $this->transactions:'Not Found'
        ];
    }
}
