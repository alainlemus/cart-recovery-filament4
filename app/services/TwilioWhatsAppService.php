<?php

    namespace App\services;

    use Twilio\Rest\Client;
    use Illuminate\Support\Facades\Log;

    class TwilioWhatsAppService
    {
        public static function sendMessage(string $to, string $message): bool
        {
            try {
                $client = new Client(
                    config('services.twilio.sid'),
                    config('services.twilio.token')
                );

                $client->messages->create(
                    "whatsapp:{$to}",
                    [
                        'from' => config('services.twilio.whatsapp_number'),
                        'body' => $message,
                    ]
                );

                return true;
            } catch (\Exception $e) {
                Log::error('Error enviando WhatsApp con Twilio', [
                    'error' => $e->getMessage(),
                ]);
                return false;
            }
        }
    }
