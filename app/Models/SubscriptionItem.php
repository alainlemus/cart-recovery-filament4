<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionItem extends Model
{
    protected $table = 'subscription_items';

    protected $fillable = [
        'subscription_id',
        'stripe_id',
        'stripe_price',
        'quantity',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
