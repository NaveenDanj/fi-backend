<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Referee extends Authenticatable
{
    use HasApiTokens , HasFactory , Notifiable;

    protected $fillable = [
        'fullname',
        'contact',
        'email',
        'password',
        'bank',
        'bankAccountNumber',
        'bankAccountName',
        'phoneVerified',
        'verification_image_1',
        'verification_image_2',
        'introducerId',
        'propic'
    ];

    protected $hidden = [
        'password'
    ];

    protected $guard = 'referee';

}
