<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Subscription;

class EnsureActiveSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user) {
            $subscription = Subscription::where('user_id', $user->id)->latest()->first();
            if ($subscription && $subscription->stripe_status === 'inactive') {
                return redirect()->route('filament.admin-shop.pages.renew-subscription');
            }
        }
        return $next($request);
    }
}
