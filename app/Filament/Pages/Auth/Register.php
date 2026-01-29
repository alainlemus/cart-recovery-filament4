<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;

class Register extends BaseRegister
{
    /**
     * After registration, redirect to billing plans instead of dashboard.
     * User needs to subscribe before accessing the dashboard.
     */
    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $user = $this->wrapInDatabaseTransaction(function () {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $user = $this->handleRegistration($data);

            $this->form->model($user)->saveRelationships();

            $this->callHook('afterRegister');

            return $user;
        });

        // Login the user
        Filament::auth()->login($user);

        session()->regenerate();

        // Redirect to billing plans instead of dashboard
        return new class implements RegistrationResponse {
            public function toResponse($request)
            {
                return redirect()->to(route('shopify.billing.plans'))
                    ->with('info', 'Welcome! Please select a plan to continue.');
            }
        };
    }
}
