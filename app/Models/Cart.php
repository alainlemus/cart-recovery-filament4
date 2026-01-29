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
        'abandoned_at',
        'currency',
        'abandoned_checkout_url',
        'recovery_token',
        'status',               // 'abandoned' o 'complete'
        'clicked_at',
        'recovered_at',
        'recovered_via',

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
