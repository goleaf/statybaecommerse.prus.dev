<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

final class LanguageController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $locale = $request->input('locale');

        // Validate locale using configured supported locales
        $supported = config('app.supported_locales', ['lt', 'en']);
        $supportedLocales = is_array($supported)
            ? $supported
            : array_filter(array_map('trim', explode(',', (string) $supported)));
        $supportedLocales = array_map('trim', $supportedLocales);

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = (string) (config('app.locale', 'lt'));
        }

        // Store locale in session
        Session::put('locale', $locale);

        // Set application locale
        app()->setLocale($locale);

        return redirect()->back()->with('success', __('admin.messages.language_changed'));
    }
}
