<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        return view('subscription.create');
    }

    public function checkout(Request $request, $plan){
        $user = Auth::user();

        return $user->newSubscription('default', $plan)
            ->checkout([
                'success_url' => route('subscription.success'),
                'cancel_url' => route('subscription.create'),
            ]);
    }

    public function success(){
        Auth::user()->update(['subscription_status' => 'active']);
        return redirect()->route('dashboard')->with('success', '¡Suscripción activada!');
    }
}
