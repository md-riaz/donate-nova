<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'amount',
        'transaction_id',
        'bkash_payment_id',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
}
