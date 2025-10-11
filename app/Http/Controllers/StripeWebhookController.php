<?php
// app/Http/Controllers/StripeWebhookController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Subscription;
use App\Models\Payment;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        $eventType = $payload['type'] ?? null;

        if ($eventType === 'invoice.payment_succeeded') {
            $invoice = $payload['data']['object'];
            $subscriptionId = $invoice['subscription'];
            $amount = $invoice['amount_paid'] / 100;
            $currency = $invoice['currency'];
            $paidAt = now();

            $subscription = Subscription::where('stripe_id', $subscriptionId)->first();
            if ($subscription) {
                Payment::create([
                    'subscription_id' => $subscription->id,
                    'amount' => $amount,
                    'currency' => $currency,
                    'paid_at' => $paidAt,
                    'stripe_invoice_id' => $invoice['id'],
                ]);
            }
        }

        // Puedes manejar otros eventos aquí

        return response()->json(['status' => 'ok']);
    }
}
