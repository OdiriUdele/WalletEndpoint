<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lga extends Model
{
    protected $table = 'lgas';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'state_id','lga'
    ];
}
