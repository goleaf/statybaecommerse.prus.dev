<?php
use App\Support\Security\Captcha\CaptchaManager;
use App\Support\Security\SuspiciousIpMonitor;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.base')] class extends Component
{
    public string $email = '';

    public ?string $captchaToken = null;

    public ?string $captchaResponse = null;

    public ?string $captchaQuestion = null;

    public function mount(CaptchaManager $captchaManager): void
    {
        $this->syncCaptchaState($captchaManager);
    }

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $captchaManager = app(CaptchaManager::class);
        $monitor = app(SuspiciousIpMonitor::class);

        $this->ensureIsNotRateLimited($captchaManager, $monitor);

        if ($captchaManager->shouldChallenge($this->throttleKey(), 'auth.password_reset')) {
            $this->syncCaptchaState($captchaManager);

            $this->validate([
                'captchaToken'    => ['required', 'string'],
                'captchaResponse' => ['required', 'string'],
            ]);

            if (! $captchaManager->verify($this->throttleKey(), 'auth.password_reset', (string) $this->captchaToken, (string) $this->captchaResponse)) {
                $this->syncCaptchaState($captchaManager, true);
                $this->captchaResponse = '';

                throw ValidationException::withMessages([
                    'captchaResponse' => __('The security check response did not match. Please try again.'),
                ]);
            }
        } else {
            $this->syncCaptchaState($captchaManager);
        }

        RateLimiter::hit($this->throttleKey(), $this->decaySeconds());

        $status = Password::sendResetLink($this->only('email'));

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            $captchaManager->markRequired($this->throttleKey(), 'auth.password_reset');
            $monitor->record($this->ipAddress(), 'password-reset', [
                'email'        => $this->email,
                'attempts'     => RateLimiter::attempts($this->throttleKey()),
                'max_attempts' => $this->maxAttempts(),
            ]);
            $this->syncCaptchaState($captchaManager, true);

            return;
        }

        $this->reset('email');

        RateLimiter::clear($this->throttleKey());
        $captchaManager->clear($this->throttleKey(), 'auth.password_reset');
        $monitor->reset($this->ipAddress(), 'password-reset');
        $this->captchaResponse = null;

        session()->flash('status', __($status));
    }

    public function hydrate(CaptchaManager $captchaManager): void
    {
        $this->syncCaptchaState($captchaManager);
    }

    public function refreshCaptcha(CaptchaManager $captchaManager): void
    {
        $this->syncCaptchaState($captchaManager, true);
    }

    private function ensureIsNotRateLimited(CaptchaManager $captchaManager, SuspiciousIpMonitor $monitor): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $this->maxAttempts())) {
            return;
        }

        $captchaManager->markRequired($this->throttleKey(), 'auth.password_reset');
        $monitor->record($this->ipAddress(), 'password-reset-rate-limit', [
            'email'        => $this->email,
            'attempts'     => RateLimiter::attempts($this->throttleKey()),
            'max_attempts' => $this->maxAttempts(),
        ]);
        $this->syncCaptchaState($captchaManager, true);

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => (int) ceil($seconds / 60),
            ]),
        ]);
    }

    private function syncCaptchaState(CaptchaManager $captchaManager, bool $forceRefresh = false): void
    {
        if (! $captchaManager->shouldChallenge($this->throttleKey(), 'auth.password_reset')) {
            $this->resetCaptcha();

            return;
        }

        $challenge = $captchaManager->challenge($this->throttleKey(), 'auth.password_reset', $forceRefresh);

        if ($challenge === null) {
            $this->resetCaptcha();

            return;
        }

        $questionChanged = $this->captchaQuestion !== $challenge->question();

        $this->captchaQuestion = $challenge->question();
        $this->captchaToken = $challenge->token();

        if ($forceRefresh || $questionChanged) {
            $this->captchaResponse = '';
        }
    }

    private function throttleKey(): string
    {
        $ip = request()->ip();
        $ipAddress = is_string($ip) && $ip !== '' ? $ip : 'unknown';

        return Str::transliterate('password-reset|' . Str::lower($this->email) . '|' . $ipAddress);
    }

    private function resetCaptcha(): void
    {
        $this->captchaQuestion = null;
        $this->captchaToken = null;
        $this->captchaResponse = null;
    }

    private function ipAddress(): string
    {
        $ip = request()->ip();

        return is_string($ip) && $ip !== '' ? $ip : 'unknown';
    }

    private function maxAttempts(): int
    {
        return max(1, (int) data_get(config('security.rate_limiting.auth.password_reset'), 'max_attempts', 5));
    }

    private function decaySeconds(): int
    {
        return max(1, (int) data_get(config('security.rate_limiting.auth.password_reset'), 'decay_seconds', 300));
    }
}; ?>

@section('meta')
    <x-meta
        :title="__('Forgot password') . ' - ' . config('app.name')"
        :description="__('Request a secure link to reset your Statybae Commerce account password')"
        canonical="{{ url()->current() }}" />
@endsection

<x-auth-page :max-width="'max-w-4xl'">
    <div class="space-y-10">
        <div class="space-y-4 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-400 to-blue-500 shadow-lg">
                <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.104 0 2-.896 2-2s-.896-2-2-2-2 .896-2 2 .896 2 2 2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11c0 5-7 10-7 10s-7-5-7-10a7 7 0 1114 0z" />
                </svg>
            </div>

            <div class="space-y-2">
                <h1 class="text-3xl font-bold text-slate-900 sm:text-4xl">
                    {{ __('Need a reset link?') }}
                </h1>
                <p class="text-base text-slate-600">
                    {{ __('Enter the email associated with your account and we will send you a secure reset link right away.') }}
                </p>
            </div>
        </div>

        <div class="space-y-6">
            <x-auth-session-status class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

            <form wire:submit="sendPasswordResetLink" class="space-y-6">
                <div class="space-y-2">
                    <x-forms.label for="email" :value="__('Email address')" />
                    <x-forms.input
                        id="email"
                        type="email"
                        wire:model.defer="email"
                        autocomplete="email"
                        autofocus
                        class="rounded-xl border border-slate-200 bg-white/90 px-4 py-3 text-base shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                        placeholder="{{ __('you@example.com') }}"
                    />
                    <x-forms.errors :messages="$errors->get('email')" class="mt-1" />
                </div>

                @if ($captchaQuestion)
                    <div class="space-y-2">
                        <x-forms.label for="reset-captcha" :value="__('Security check')" />
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-600">
                            <span>{{ $captchaQuestion }}</span>
                            <button
                                type="button"
                                wire:click="refreshCaptcha"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1 rounded-lg bg-white/70 px-2 py-1 text-xs font-semibold text-indigo-600 shadow-sm transition hover:bg-white"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12a7.5 7.5 0 0112.79-5.303M19.5 12a7.5 7.5 0 01-12.79 5.303" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 8.25H4.5v-3.75M19.5 15.75V19.5h-3.75" />
                                </svg>
                                <span>{{ __('New question') }}</span>
                            </button>
                        </div>
                        <x-forms.input
                            id="reset-captcha"
                            type="text"
                            wire:model.defer="captchaResponse"
                            autocomplete="off"
                            class="rounded-xl border border-slate-200 bg-white/90 px-4 py-3 text-base shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                            placeholder="{{ __('Enter the answer') }}"
                        />
                        <input type="hidden" wire:model="captchaToken" />
                        <x-forms.errors :messages="$errors->get('captchaResponse')" class="mt-1" />
                    </div>
                @endif

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="group relative flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 via-indigo-500 to-blue-500 px-5 py-3.5 text-base font-semibold text-white shadow-lg transition hover:from-indigo-600 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-indigo-400/40 focus:ring-offset-2 focus:ring-offset-white disabled:cursor-not-allowed disabled:opacity-70"
                >
                    <span wire:loading.remove>
                        {{ __('Email password reset link') }}
                    </span>
                    <span wire:loading class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('Sending...') }}
                    </span>
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-5 py-4 text-center text-sm text-slate-600">
            {{ __('Remembered your password?') }}
            <x-link :href="route('login')" class="font-semibold text-indigo-600 hover:text-indigo-700">
                {{ __('Return to sign in') }}
            </x-link>
        </div>

        <p class="text-center text-xs text-slate-400">
            {{ __('Still need help?') }}
            <x-link href="#" class="text-indigo-500 hover:text-indigo-600">{{ __('Contact support') }}</x-link>
            {{ __('and we will make sure you are back in quickly.') }}
        </p>
    </div>
</x-auth-page>
