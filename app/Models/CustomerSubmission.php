<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company',
        'contact',
        'email',
        'salary',
        'lat',
        'long',

        'refereeId',
        'status',
        'remarks',

        'consent_of_lead',
        'contacted_by_FCB',
        'statusRemarks'

    ];

}
