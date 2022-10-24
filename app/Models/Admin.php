<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Authenticatable
{
    use HasApiTokens , HasFactory;

    protected $fillable = [
        'fullname',
        'email',
        'password'
    ];


    protected $hidden = [
        'password'
    ];

}
