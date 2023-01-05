<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;


class Admin extends Authenticatable
{
    use HasApiTokens , HasFactory;

    protected $fillable = [
        'fullname',
        'email',
        'password',
        'role'
    ];


    protected $hidden = [
        'password'
    ];

    protected $guard = 'admin';

}
