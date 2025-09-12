<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_cart',
        'shop_id',
        'user_id',
        'shopify_id',           // ID del checkout en Shopify
        'email_client',
        'phone_client',
        'response',
        'total_price',
        'status',
        'abandoned_at',
        'abandoned_checkout_url',
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
