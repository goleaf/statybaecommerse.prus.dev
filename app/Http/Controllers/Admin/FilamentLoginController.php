<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class FilamentLoginController
{
    public function __invoke(Request $request): RedirectResponse
    {
        if (Filament::auth()->check()) {
            return redirect()->intended(Filament::getUrl());
        }

        $validated = $request->validate([
            'data.email'    => ['required', 'email'],
            'data.password' => ['required', 'string'],
            'data.remember' => ['nullable', 'boolean'],
        ]);

        $data = $validated['data'] ?? [];
        $remember = (bool) ($data['remember'] ?? false);

        if (! Filament::auth()->attempt([
            'email'    => $data['email'] ?? '',
            'password' => $data['password'] ?? '',
        ], $remember)) {
            return $this->redirectWithFailure($data);
        }

        $user = Filament::auth()->user();
        $panel = Filament::getCurrentPanel();

        if ($user instanceof FilamentUser && $panel !== null && ! $user->canAccessPanel($panel)) {
            Filament::auth()->logout();

            return $this->redirectWithFailure($data);
        }

        $request->session()->regenerate();

        return redirect()->intended(Filament::getUrl());
    }

    /**
     * @param array<string, mixed> $data
     */
    private function redirectWithFailure(array $data): RedirectResponse
    {
        return back()
            ->withErrors(['data.email' => __('filament-panels::pages/auth/login.messages.failed')])
            ->withInput([
                'data' => [
                    'email'    => $data['email'] ?? null,
                    'remember' => $data['remember'] ?? null,
                ],
            ]);
    }
}
