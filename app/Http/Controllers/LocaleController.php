<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * Change the application locale.
     */
    public function change(Request $request, string $locale)
    {
        \Log::info('LocaleController::change called', [
            'locale' => $locale,
            'user_id' => $request->user()?->id,
            'session_id' => session()->getId(),
        ]);

        // Validate locale
        if (! in_array($locale, config('app.available_locales', ['es', 'en']))) {
            \Log::warning('Invalid locale attempted', ['locale' => $locale]);

            return redirect()->back()->with('error', 'Invalid locale');
        }

        // Set locale in session
        Session::put('locale', $locale);
        \Log::info('Locale set in session', ['locale' => $locale]);

        // If user is authenticated, save preference
        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
            \Log::info('Locale saved to user', ['user_id' => $request->user()->id, 'locale' => $locale]);
        }

        // Set current locale
        App::setLocale($locale);
        \Log::info('App locale set', ['locale' => $locale, 'app_locale' => app()->getLocale()]);

        return redirect()->back();
    }
}
