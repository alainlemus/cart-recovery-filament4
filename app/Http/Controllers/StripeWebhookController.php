<?php

// app/Http/Controllers/StripeWebhookController.php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        // Stripe envía el objeto invoice directamente bajo "object"
        if (isset($payload['object']) && is_array($payload['object']) && ($payload['object']['object'] ?? null) === 'invoice') {
            $invoice = $payload['object'];

            Log::info('Received Stripe invoice webhook: '.json_encode($invoice));

            // Solo procesar si es un pago exitoso de suscripción
            if (
                ($invoice['billing_reason'] ?? null) === 'subscription_cycle'
                && ($invoice['status'] ?? null) === 'paid'
            ) {
                Log::info('Processing paid subscription invoice: '.json_encode($invoice));
                $subscriptionId = $invoice['subscription'] ?? null;
                $amount = isset($invoice['amount_paid']) ? $invoice['amount_paid'] / 100 : null;
                $currency = $invoice['currency'] ?? null;
                $stripeInvoiceId = $invoice['id'] ?? null;
                $paidAt = isset($invoice['status_transitions']['paid_at'])
                    ? \Carbon\Carbon::createFromTimestamp($invoice['status_transitions']['paid_at'])
                    : now();

                if ($subscriptionId && $amount && $currency && $stripeInvoiceId) {
                    $subscription = Subscription::where('stripe_id', $subscriptionId)->first();

                    Log::info("Found subscription for ID {$subscriptionId}: ".($subscription ? 'Yes' : 'No'));

                    if ($subscription && ! Payment::where('stripe_invoice_id', $stripeInvoiceId)->exists()) {
                        Log::info('Creating payment record for subscription ID: '.$subscription->id);
                        Payment::create([
                            'subscription_id' => $subscription->id,
                            'amount' => $amount,
                            'currency' => $currency,
                            'paid_at' => $paidAt,
                            'stripe_invoice_id' => $stripeInvoiceId,
                        ]);
                        Log::info('Payment record created for subscription ID: '.$subscription->id);
                    }
                }
            } else {
                Log::info('Ignoring non-paid or non-subscription invoice: '.json_encode($invoice));
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
