<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{

    protected $table = 'wallets';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'wallet_name','user_id','wallet_type_id','balance',
    ];

    public function type(){
        return $this->belongsTo(WalletType::class,'wallet_type_id','id');
    }
    

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
    

    public function transactions(){
        return $this->hasMany(WalletTransaction::class);
    }
}
