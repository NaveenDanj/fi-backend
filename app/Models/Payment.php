<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'referee_id',
        'type',
        'amount',
        'status',
        'checked_by',
        'pdf_link'
    ];

}
