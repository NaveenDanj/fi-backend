<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefereeSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'submissionType',
        'submissionId',
        'refereeId',
        'status'
    ];

}
