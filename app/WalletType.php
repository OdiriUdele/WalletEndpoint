<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WalletType extends Model
{
    protected $table = 'wallet_types';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'wallet_type','minimum_balance','monthly_interest_rate'
    ];
}
