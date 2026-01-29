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
        // Validate locale
        if (! in_array($locale, config('app.available_locales', ['es', 'en']))) {
            return redirect()->back()->with('error', 'Invalid locale');
        }

        // Set locale in session
        Session::put('locale', $locale);

        // If user is authenticated, save preference
        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }

        // Set current locale
        App::setLocale($locale);

        return redirect()->back()->with('success', __('messages.language_changed'));
    }
}
