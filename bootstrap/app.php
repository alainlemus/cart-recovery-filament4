<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens([
            '/webhooks/orders/create',
            '/webhooks/checkouts/create',
            '/webhooks/stripe',
            '/webhooks/shopify/billing',
        ]);

        $middleware->alias([
            'shopify.subscribed' => \App\Http\Middleware\EnsureShopifySubscriptionActive::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        // Redirigir al login de Filament cuando no está autenticado
        $middleware->redirectGuestsTo('/admin-shop/login');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
