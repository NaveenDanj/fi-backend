<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class forgotPassword extends Model
{
    use HasFactory;

    protected $fillable = [
        'otp',
        'token',
        'refereeId',
    ];


}
