<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefereeOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'otp',
        'expireTime',
        'blocked'
    ];

}
