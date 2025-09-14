<?php

declare(strict_types=1);

namespace App\Livewire\Actions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Logout
 * 
 * Livewire component for reactive frontend functionality.
 */
class Logout
{
    /**
     * Log the current user out of the application and redirect.
     */
    public function __invoke(): RedirectResponse
    {
        Auth::guard('web')->logout();

        // Clear any impersonation/session state
        Session::forget(['impersonate', 'original_user']);
        Session::invalidate();
        Session::regenerateToken();

        return redirect()->route('home');
    }
}
