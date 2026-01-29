<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Priority order:
        // 1. User's saved preference
        // 2. Session locale
        // 3. Browser's accept-language header
        // 4. App default locale

        $locale = null;

        // 1. Check authenticated user's preference
        if ($request->user() && $request->user()->locale) {
            $locale = $request->user()->locale;
        }
        // 2. Check session
        elseif (Session::has('locale')) {
            $locale = Session::get('locale');
        }
        // 3. Check browser preference
        elseif ($request->header('Accept-Language')) {
            $browserLocale = substr($request->header('Accept-Language'), 0, 2);
            if (in_array($browserLocale, config('app.available_locales', ['es', 'en']))) {
                $locale = $browserLocale;
            }
        }

        // Validate locale and set
        if ($locale && in_array($locale, config('app.available_locales', ['es', 'en']))) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
