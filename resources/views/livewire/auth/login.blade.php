@section('meta')
    <x-meta
        :title="__('Log in') . ' - ' . config('app.name')"
        :description="__('Access your account to track orders, manage addresses, and more')"
        canonical="{{ url()->current() }}" />
@endsection

<x-auth-page>
    <x-slot:aside>
        <div class="flex h-full flex-col justify-between">
            <div class="space-y-8">
                <div class="space-y-4">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white/70">
                        {{ __('Member perks') }}
                    </span>

                    <h2 class="text-3xl font-semibold leading-tight text-white">
                        {{ __('Stay close to your purchases with an account that works for you') }}
                    </h2>

                    <p class="text-white/80 text-sm leading-relaxed">
                        {{ __('Save your favourite products, receive tailored recommendations, and keep every order in one beautifully organised place.') }}
                    </p>
                </div>

                <div class="space-y-6">
                    <div class="flex gap-4">
                        <div class="mt-1 flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <p class="text-base font-medium text-white">{{ __('Real-time order tracking') }}</p>
                            <p class="text-sm text-white/75">{{ __('Know exactly where every parcel is, from processing to your doorstep.') }}</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="mt-1 flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 01-8 0M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <p class="text-base font-medium text-white">{{ __('Personalised dashboard') }}</p>
                            <p class="text-sm text-white/75">{{ __('Manage addresses, communications, and wishlists with a single secure login.') }}</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="mt-1 flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 22a10 10 0 100-20 10 10 0 000 20z" />
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <p class="text-base font-medium text-white">{{ __('Faster repeat purchases') }}</p>
                            <p class="text-sm text-white/75">{{ __('Enable one-click reorders and keep your favourite bundles ready to go.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 rounded-2xl border border-white/20 bg-white/5 p-6 shadow-lg">
                <p class="text-sm text-white/80">
                    <span class="font-semibold text-white">{{ __('New here?') }}</span>
                    {{ __('Create a free account to unlock member-only pricing and curated collections just for you.') }}
                </p>
                <div class="mt-4">
                    <x-link :href="route('register')" class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-white/30">
                        {{ __('Create account') }}
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </x-link>
                </div>
            </div>
        </div>
    </x-slot:aside>

    <div class="space-y-10">
        <div class="space-y-4 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-400 to-blue-500 shadow-lg">
                <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>

            <div class="space-y-2">
                <h1 class="text-3xl font-bold text-slate-900 sm:text-4xl">
                    {{ __('Welcome back') }}
                </h1>
                <p class="text-base text-slate-600">
                    {{ __('Log in to access your account dashboard and continue your shopping journey.') }}
                </p>
            </div>
        </div>

        <div class="space-y-8">
            <x-auth-session-status class="rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-700" :status="session('status')" />

            <form wire:submit="login" class="space-y-6">
                <div class="space-y-5">
                    <div class="space-y-2">
                        <x-forms.label for="email" :value="__('Email address')" />
                        <x-forms.input
                            id="email"
                            type="email"
                            wire:model.defer="loginForm.email"
                            autocomplete="email"
                            autofocus
                            class="rounded-xl border border-slate-200 bg-white/90 px-4 py-3 text-base shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                            placeholder="{{ __('you@example.com') }}"
                        />
                        <x-forms.errors :messages="$errors->get('loginForm.email')" class="mt-1" />
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <x-forms.label for="password" :value="__('Password')" />
                            <x-link :href="route('password.request')" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                                {{ __('Forgot your password?') }}
                            </x-link>
                        </div>
                        <x-forms.input
                            id="password"
                            type="password"
                            wire:model.defer="loginForm.password"
                            autocomplete="current-password"
                            class="rounded-xl border border-slate-200 bg-white/90 px-4 py-3 text-base shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                            placeholder="••••••••"
                        />
                        <x-forms.errors :messages="$errors->get('loginForm.password')" class="mt-1" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4">
                    <label class="inline-flex items-center gap-3 text-sm text-slate-600">
                        <input
                            type="checkbox"
                            wire:model.defer="loginForm.remember"
                            class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        >
                        <span>{{ __('Keep me signed in on this device') }}</span>
                    </label>

                    <p class="text-xs text-slate-400">
                        {{ __('Securely encrypted using TLS 1.3') }}
                    </p>
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="group relative flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 via-indigo-500 to-blue-500 px-5 py-3.5 text-base font-semibold text-white shadow-lg transition hover:from-indigo-600 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-indigo-400/40 focus:ring-offset-2 focus:ring-offset-white disabled:cursor-not-allowed disabled:opacity-70"
                >
                    <span wire:loading.remove>
                        {{ __('Sign in') }}
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

        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-5 py-4 text-center text-sm text-slate-600">
            {{ __("Don't have an account yet?") }}
            <x-link :href="route('register')" class="font-semibold text-indigo-600 hover:text-indigo-700">
                {{ __('Create one in minutes') }}
            </x-link>
        </div>

        <p class="text-center text-xs text-slate-400">
            {{ __('By signing in you agree to our') }}
            <x-link href="#" class="text-indigo-500 hover:text-indigo-600">{{ __('Terms of Service') }}</x-link>
            {{ __('and') }}
            <x-link href="#" class="text-indigo-500 hover:text-indigo-600">{{ __('Privacy Policy') }}</x-link>.
        </p>
    </div>
</x-auth-page>
