@section('meta')
    <x-meta
        :title="__('Log in') . ' - ' . config('app.name')"
        :description="__('Access your account to track orders, manage addresses, and more')"
        canonical="{{ url()->current() }}" />
@endsection

<x-auth-page>
    <x-slot:aside>
        <div class="flex h-full flex-col justify-between text-white">
            <div class="space-y-10">
                <div class="space-y-5">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-white/70">
                        {{ __('Trade advantages') }}
                    </span>

                    <h2 class="text-4xl font-semibold leading-tight">
                        {{ __('Plan every build with a single source of truth') }}
                    </h2>

                    <p class="text-sm leading-relaxed text-white/75">
                        {{ __('Track procurement, organise subcontractors, and keep your material orders aligned with site timelines in one secure dashboard.') }}
                    </p>
                </div>

                <div class="grid gap-5">
                    <div class="rounded-3xl border border-white/25 bg-white/10 p-6 shadow-xl shadow-blue-900/20 backdrop-blur-sm">
                        <div class="flex items-center justify-between text-[0.65rem] uppercase tracking-[0.32em] text-white/60">
                            <span>{{ __('Reliability score') }}</span>
                            <svg class="h-4 w-4 text-white/70" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </div>
                        <p class="mt-3 text-3xl font-semibold text-white">98.4%</p>
                        <p class="mt-2 text-sm text-white/75">
                            {{ __('Average on-time deliveries recorded across our European fulfilment partners last quarter.') }}
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/20 bg-white/10 p-4">
                            <div class="flex items-start gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M5 11h14M7 15h10M9 19h6" />
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-semibold text-white">{{ __('Coordinated logistics') }}</p>
                                    <p class="text-xs text-white/70">{{ __('Synchronise split deliveries and track crane slots in real time with unified updates.') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/20 bg-white/10 p-4">
                            <div class="flex items-start gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a4.5 4.5 0 014.5 4.5v.75l1.5 1.5v2.25h-12v-2.25l1.5-1.5v-.75a4.5 4.5 0 014.5-4.5z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 18.75h6" />
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-semibold text-white">{{ __('Specialist support') }}</p>
                                    <p class="text-xs text-white/70">{{ __('Lean on procurement experts for sourcing complex materials exactly when sites need them.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 space-y-4 rounded-3xl border border-white/20 bg-gradient-to-br from-white/20 via-white/5 to-transparent p-6 shadow-2xl shadow-indigo-900/30 backdrop-blur">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white/15">
                        <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ __('“Site coordination is easier than ever.”') }}</p>
                        <p class="text-xs text-white/70">{{ __('Monika, Project Manager — Statyba Partner Network') }}</p>
                    </div>
                </div>
                <p class="text-sm text-white/75">
                    {{ __('Join thousands of construction professionals connecting material schedules, approvals, and payments through Statyba Commerce.') }}
                </p>
                <x-link :href="route('register')" class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">
                    {{ __('Create a free account') }}
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </x-link>
            </div>
        </div>
    </x-slot:aside>

    <div class="space-y-12">
        <div class="space-y-4 text-center">
            <span class="mx-auto inline-flex items-center gap-2 rounded-full bg-slate-900/5 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">
                {{ __('Secure access') }}
            </span>

            <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                {{ __('Sign in to Statyba Commerce') }}
            </h1>

            <p class="max-w-xl mx-auto text-base text-slate-600">
                {{ __('Review live inventory, approve quotes, and keep every site moving from a single workspace.') }}
            </p>
        </div>

        <div class="space-y-8">
            <div class="grid gap-3 rounded-2xl border border-slate-200/70 bg-slate-50 px-5 py-3 text-xs font-semibold text-slate-600 sm:grid-cols-3">
                <div class="flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 22a10 10 0 100-20 10 10 0 000 20z" />
                    </svg>
                    <span>{{ __('Real-time tracking') }}</span>
                </div>
                <div class="flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.75 5.75h12.5v12.5H5.75z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.5v3M14.25 3.5v3M9.75 17.5v3M14.25 17.5v3M3.5 9.75h3M17.5 9.75h3M3.5 14.25h3M17.5 14.25h3" />
                    </svg>
                    <span>{{ __('Site-ready schedules') }}</span>
                </div>
                <div class="flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 8.25h12M6 12h12M6 15.75h12" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 5.25h4.5v2.5h-4.5z" />
                    </svg>
                    <span>{{ __('Invoice alignment') }}</span>
                </div>
            </div>

            <x-auth-session-status class="rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-700" :status="session('status')" />

            <form wire:submit="login" class="space-y-7">
                <div class="space-y-6">
                    <div class="space-y-2">
                        <x-forms.label for="email" :value="__('Email address')" />
                        <x-forms.input
                            id="email"
                            type="email"
                            wire:model.defer="loginForm.email"
                            autocomplete="email"
                            autofocus
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                            placeholder="{{ __('you@example.com') }}"
                        />
                        <x-forms.errors :messages="$errors->get('loginForm.email')" class="mt-1" />
                    </div>

                    <div class="space-y-2" x-data="{ showPassword: false }">
                        <div class="flex items-center justify-between">
                            <x-forms.label for="password" :value="__('Password')" />
                            <x-link :href="route('password.request')" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                                {{ __('Forgot your password?') }}
                            </x-link>
                        </div>
                        <div class="relative">
                            <x-forms.input
                                id="password"
                                type="password"
                                x-bind:type="showPassword ? 'text' : 'password'"
                                x-ref="passwordField"
                                wire:model.defer="loginForm.password"
                                autocomplete="current-password"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-12 text-base shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-3 flex items-center text-slate-500 transition hover:text-indigo-500"
                                x-on:click="showPassword = !showPassword"
                                x-bind:aria-label="showPassword ? @js(__('Hide password')) : @js(__('Show password'))"
                                x-bind:title="showPassword ? @js(__('Hide password')) : @js(__('Show password'))"
                                x-bind:aria-pressed="showPassword ? 'true' : 'false'"
                            >
                                <svg x-show="!showPassword" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12S5.25 5.25 12 5.25 21.75 12 21.75 12 18.75 18.75 12 18.75 2.25 12 2.25 12z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 9.88A3 3 0 0114.12 14.12" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.44 6.47A10.97 10.97 0 001.5 12S4.5 18.75 11.25 18.75c1.64 0 3.15-.33 4.48-.9" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.59 4.36A11 11 0 0112 4.25c6.75 0 9.75 7.75 9.75 7.75a17.35 17.35 0 01-3.48 4.7" />
                                </svg>
                            </button>
                        </div>
                        <x-forms.errors :messages="$errors->get('loginForm.password')" class="mt-1" />
                    </div>

                    @if ($loginForm->captchaQuestion)
                        <div class="space-y-2">
                            <x-forms.label for="captcha" :value="__('Security check')" />
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-600">
                                <span>{{ $loginForm->captchaQuestion }}</span>
                                <button
                                    type="button"
                                    wire:click="refreshCaptcha"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-1 text-xs font-semibold text-indigo-600 shadow-sm transition hover:bg-slate-100"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12a7.5 7.5 0 0112.79-5.303M19.5 12a7.5 7.5 0 01-12.79 5.303" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 8.25H4.5v-3.75M19.5 15.75V19.5h-3.75" />
                                    </svg>
                                    <span>{{ __('New question') }}</span>
                                </button>
                            </div>
                            <x-forms.input
                                id="captcha"
                                type="text"
                                wire:model.defer="loginForm.captchaResponse"
                                autocomplete="off"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                                placeholder="{{ __('Enter the answer') }}"
                            />
                            <input type="hidden" wire:model="loginForm.captchaToken" />
                            <x-forms.errors :messages="$errors->get('loginForm.captchaResponse')" class="mt-1" />
                        </div>
                    @endif
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <label class="inline-flex items-center gap-3 text-sm text-slate-600">
                        <input
                            type="checkbox"
                            wire:model.defer="loginForm.remember"
                            class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        >
                        <span>{{ __('Keep me signed in on this device') }}</span>
                    </label>

                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <svg class="h-3.5 w-3.5 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
                        </svg>
                        <span>{{ __('Encrypted with TLS 1.3') }}</span>
                    </div>
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="group flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3.5 text-base font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:ring-offset-2 focus:ring-offset-white disabled:cursor-not-allowed disabled:opacity-70"
                >
                    <span wire:loading.remove class="inline-flex items-center gap-2">
                        {{ __('Sign in') }}
                        <svg class="h-5 w-5 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                        </svg>
                    </span>
                    <span wire:loading class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('Signing in...') }}
                    </span>
                </button>
            </form>

            <div class="space-y-5">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="bg-white px-4 text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">
                            {{ __('Continue with') }}
                        </span>
                    </div>
                </div>

                <div class="grid gap-3">
                    <x-auth-oauth />
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-center text-sm text-slate-600">
            {{ __("Don't have an account yet?") }}
            <x-link :href="route('register')" class="font-semibold text-indigo-600 hover:text-indigo-700">
                {{ __('Create one in minutes') }}
            </x-link>
        </div>

        <p class="text-center text-xs text-slate-400">
            {{ __('frontend.legal.login_agreement_intro') }}
            <x-link href="{{ route('frontend.legal.terms') }}" class="text-indigo-500 hover:text-indigo-600">
                {{ __('frontend.legal.terms_of_service') }}
            </x-link>
            {{ __('frontend.legal.and') }}
            <x-link href="{{ route('frontend.legal.privacy') }}" class="text-indigo-500 hover:text-indigo-600">
                {{ __('frontend.legal.privacy_policy') }}
            </x-link>.
        </p>
    </div>
</x-auth-page>
