<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id','order_status','order_status_payment','total_amount','note'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

     protected static function booted()
    {
        static::creating(function (Order $order) {
            if (empty($order->order_code)) {
                $order->order_code = self::generateUniqueCode(8);
            }
        });
    }

    protected static function randomPrettyCode(int $len = 8): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789'; 
        $max = strlen($alphabet) - 1;
        $out = '';
        for ($i = 0; $i < $len; $i++) {
            $out .= $alphabet[random_int(0, $max)];
        }
        return $out;
    }

    protected static function generateUniqueCode(int $len = 8): string
    {
        for ($i = 0; $i < 5; $i++) {
            $code = self::randomPrettyCode($len);
            if (! self::where('order_code', $code)->exists()) {
                return $code;
            }
        }
        return self::randomPrettyCode($len);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}

