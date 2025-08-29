<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_id',
        'items',
        'total_price',
        'status',
        'abandoned_at',
        'email_client',
        'phone_client',
    ];

    protected $casts = [
        'items' => 'array',
        'abandoned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

}
