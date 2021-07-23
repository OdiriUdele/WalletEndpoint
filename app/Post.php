<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{

    protected $table = 'posts';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'body',
    ];
}
