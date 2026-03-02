<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Legal;
use Illuminate\View\View;

class LegalController extends Controller
{
    public function privacy(): View
    {
        $legal = Legal::query()
            ->with('translations')
            ->firstWhere('key', 'privacy-policy');

        return view('frontend.legal.privacy', compact('legal'));
    }

    public function terms(): View
    {
        $baseQuery = Legal::query()->with('translations');

        $legal = (clone $baseQuery)->firstWhere('key', 'terms-of-use');

        if (! $legal instanceof Legal) {
            // Keep backwards compatibility with installations that used `terms`
            // instead of `terms-of-use` as the legal document key.
            $legal = (clone $baseQuery)->firstWhere('key', 'terms');
        }

        return view('frontend.legal.terms', compact('legal'));
    }

    public function cookies(): View
    {
        $legal = Legal::query()
            ->with('translations')
            ->firstWhere('key', 'cookie-policy');

        return view('frontend.legal.cookies', compact('legal'));
    }

    public function shipping(): View
    {
        $legal = Legal::query()
            ->with('translations')
            ->firstWhere('key', 'shipping-policy');

        return view('frontend.legal.shipping', compact('legal'));
    }

    public function returns(): View
    {
        $legal = Legal::query()
            ->with('translations')
            ->firstWhere('key', 'return-policy');

        return view('frontend.legal.returns', compact('legal'));
    }
}
