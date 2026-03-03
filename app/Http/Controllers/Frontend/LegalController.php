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
        $legal = $this->findByPreferredKeys([
            'privacy-policy',
            'privacy',
        ]);

        return view('frontend.legal.privacy', compact('legal'));
    }

    public function terms(): View
    {
        $legal = $this->findByPreferredKeys([
            'terms-of-use',
            'terms',
        ]);

        return view('frontend.legal.terms', compact('legal'));
    }

    public function cookies(): View
    {
        $legal = $this->findByPreferredKeys([
            'cookie-policy',
            'cookies',
        ]);

        return view('frontend.legal.cookies', compact('legal'));
    }

    public function shipping(): View
    {
        $legal = $this->findByPreferredKeys([
            'shipping-policy',
            'shipping',
        ]);

        return view('frontend.legal.shipping', compact('legal'));
    }

    public function returns(): View
    {
        $legal = $this->findByPreferredKeys([
            'return-policy',
            'returns',
            'refund-policy',
            'refund',
        ]);

        return view('frontend.legal.returns', compact('legal'));
    }

    /**
     * @param array<int, string> $keys
     */
    private function findByPreferredKeys(array $keys): ?Legal
    {
        if ($keys === []) {
            return null;
        }

        $query = Legal::query()->with('translations');

        foreach ($keys as $key) {
            $legal = (clone $query)->firstWhere('key', $key);

            if ($legal instanceof Legal) {
                return $legal;
            }
        }

        return null;
    }
}
