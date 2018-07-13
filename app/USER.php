<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class USER extends Model
{
    //

    use SoftDeletes;

    protected $table = 'user';

    protected $primaryKey = 'id';

    protected $fillable = [
        'token',
        'email',
        'password'
    ];

    protected $hidden = [
        'created_at'
    ];

    protected $dates = ['deleted_at'];
}
