<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Billable, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Panel admin (solo super-admin)
        if ($panel->getId() === 'admin') {
            return $this->role === 'super-admin';
        }

        // Panel admin-shop (solo admin o user con suscripción activa)
        if ($panel->getId() === 'admin-shop') {
            if (! in_array($this->role, ['admin', 'user'])) {
                return false;
            }

            // Check for Stripe subscription
            $hasStripeSubscription = $this->subscriptions()
                ->where('stripe_status', 'active')
                ->exists();

            if ($hasStripeSubscription) {
                return true;
            }

            // Check for Shopify subscription
            $shop = $this->shops()->first();
            if ($shop && $shop->hasActiveShopifySubscription()) {
                return true;
            }

            return false;
        }

        // Otros paneles o default
        return false;
    }

    public function shops()
    {
        return $this->hasMany(Shop::class);
    }
}
