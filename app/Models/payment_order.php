<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class payment_order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'bank_code', 'bank_account_number', 'bank_account_name',
        'content', 'amount',
        'qr_code', 'qr_link', 'img_id',
        'transaction_ref_id',
        'paid_at',
    ];

    protected $casts = [
        'paid_at'      => 'datetime',
        'amount'       => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
