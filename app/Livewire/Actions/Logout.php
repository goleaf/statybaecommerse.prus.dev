<?php

declare(strict_types=1);

namespace App\Livewire\Actions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Logout
 *
 * Livewire component for Logout with reactive frontend functionality, real-time updates, and user interaction handling.
 */
class Logout
{
    /**
     * Handle __invoke functionality with proper error handling.
     */
    public function __invoke(): RedirectResponse
    {
        Auth::guard('web')->logout();
        Session::invalidate();
        Session::regenerateToken();

        return redirect()->route('home');
    }
}
