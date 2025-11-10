<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package_store extends Model
{
    protected $table = 'package_store';   // <-- quan trọng
    public $timestamps = true;    
     protected $fillable = [
        'package_id','store_id',
        'starts_at','expires_at','price_override','status'
    ];
}
