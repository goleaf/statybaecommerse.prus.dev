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
        $legal = Legal::where('slug', 'privacy-policy')->first();

        return view('frontend.legal.privacy', compact('legal'));
    }

    public function terms(): View
    {
        $legal = Legal::where('slug', 'terms-of-service')->first();

        return view('frontend.legal.terms', compact('legal'));
    }

    public function cookies(): View
    {
        $legal = Legal::where('slug', 'cookie-policy')->first();

        return view('frontend.legal.cookies', compact('legal'));
    }

    public function returns(): View
    {
        $legal = Legal::where('slug', 'return-policy')->first();

        return view('frontend.legal.returns', compact('legal'));
    }
}
