<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referee extends Authenticatable
{
    use HasApiTokens , HasFactory;

    protected $fillable = [
        'fullname',
        'contact',
        'email',
        'password',
        'ppcopy',
        'visapage',
        'emiratesIdFront',
        'emiratesIdBack',
        'bank',
        'bankAccountNumber',
        'bankAccountName',
        'phoneVerified'
    ];

}
