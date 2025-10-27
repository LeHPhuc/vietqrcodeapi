<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class PaymentOrders extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'bank_account_number',
        'content',
        'amount',
        'transaction_id',
        'reference_number',
        'transaction_time',
        'qrlink',
    ];
}
