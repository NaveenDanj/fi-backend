<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'firstName',
        'lastName',
        'contact',
        'email',
        'salary',

        'passportPath',
        'visaPath',

        'idFrontPath',
        'idBackPath',

        'salarySlipPath',

        'refereeId',
        'status'


    ];

}
