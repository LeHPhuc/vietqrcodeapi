<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'duration_days',
    ];

    protected $casts = [
        'price'         => 'decimal:2',
        'duration_days' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];
    public function stores()
    {
        return $this->belongsToMany(\App\Models\Store::class, 'package_store')
            ->withPivot(['starts_at', 'expires_at', 'price_override', 'status'])
            ->withTimestamps();
    }
}
