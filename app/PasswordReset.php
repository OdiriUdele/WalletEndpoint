<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_resets';

    const UPDATED_AT = null;
    
    protected $fillable = ['email','token','created_at'];

    protected $primaryKey = "email";
}
