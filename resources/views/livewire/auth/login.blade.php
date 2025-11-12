@section('meta')
    <x-meta
        :title="__('auth_login_title') . ' - ' . config('app.name')"
        :description="__('auth_login_subtitle')"
        canonical="{{ url()->current() }}" />
@endsection

<x-auth-page class="bg-sage">
    <x-slot:aside>
        <div class="flex h-full flex-col justify-between">
            <div class="space-y-8">
                <div class="space-y-4">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/70 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-dark">
                        {{ __('frontend/home.mission.badge') }}
                    </span>

                    <h2 class="text-3xl font-semibold leading-tight text-dark">
                        {{ __('frontend/home.mission.title') }}
                    </h2>

                    <p class="text-sm leading-relaxed text-slate-800">
                        {{ __('frontend/home.mission.subtitle') }}
                    </p>
                </div>

                <div class="rounded-3xl border border-ash bg-white/70 p-6 text-dark">
                    <div class="space-y-3">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-dark">
                            {{ __('frontend/home.loyalty.badge') }}
                        </span>
                        <h3 class="text-2xl font-bold text-dark">{{ __('frontend/home.loyalty.title') }}</h3>
                        <p class="text-slate-800">{{ __('frontend/home.loyalty.subtitle') }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-10 rounded-2xl border border-ash bg-white/70 p-6">
                <p class="text-sm text-slate-800">
                    <span class="font-semibold">{{ __('auth_new_user') }}</span>
                    {{ __('auth_new_user_description') }}
                    <x-link :href="route('register')" class="ml-1 font-semibold text-dark hover:text-black/80">
                        {{ __('auth_create_account_link') }}
                    </x-link>
                </p>
            </div>
        </div>
    </x-slot:aside>

    <div class="space-y-10">
        <div class="space-y-4 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-dark text-sage shadow-lg">
                <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>

            <div class="space-y-3">
                <span class="inline-flex items-center gap-2 rounded-full bg-dark/5 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-dark">
                    {{ __('auth_account_zone') }}
                </span>
                <h1 class="text-3xl font-extrabold tracking-tight text-dark sm:text-4xl">
                    {{ __('auth_welcome_back') }}
                </h1>
                <p class="mx-auto max-w-xl text-sm text-slate-600">
                    {{ __('auth_welcome_back_subtitle') }}
                </p>
            </div>
        </div>

        <div class="space-y-8">
            <x-auth-session-status class="rounded-2xl border border-ash bg-sage px-4 py-3 text-sm text-dark" :status="session('status')" />

            <form wire:submit="login" class="space-y-6">
                <div class="space-y-5">
                    <div class="space-y-2">
                        <x-forms.label for="email" :value="__('auth_email')" />
                        <x-forms.input
                            id="email"
                            type="email"
                            wire:model.defer="loginForm.email"
                            autocomplete="email"
                            autofocus
                            class="rounded-xl border border-ash bg-white px-4 py-3 text-base shadow-sm transition focus:border-dark focus:ring-2 focus:ring-dark/10"
                            placeholder="{{ __('auth_email_placeholder') }}"
                        />
                        <x-forms.errors :messages="$errors->get('loginForm.email')" class="mt-1" />
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <x-forms.label for="password" :value="__('auth_password')" />
                            <x-link :href="route('password.request')" class="text-sm font-semibold text-dark hover:text-stone">
                                {{ __('auth_forgot_password') }}
                            </x-link>
                        </div>
                        <x-forms.input
                            id="password"
                            type="password"
                            wire:model.defer="loginForm.password"
                            autocomplete="current-password"
                            class="rounded-xl border border-ash bg-white px-4 py-3 text-base shadow-sm transition focus:border-dark focus:ring-2 focus:ring-dark/10"
                            placeholder="{{ __('auth_password_placeholder') }}"
                        />
                        <x-forms.errors :messages="$errors->get('loginForm.password')" class="mt-1" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4">
                    <label class="inline-flex items-center gap-3 text-sm text-slate-600">
                        <input
                            type="checkbox"
                            wire:model.defer="loginForm.remember"
                            class="h-4 w-4 rounded border-ash text-dark focus:ring-dark"
                        >
                        <span>{{ __('auth_remember_me') }}</span>
                    </label>

                    <p class="text-xs text-slate-500">{{ __('auth_secure_notice') }}</p>
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="group relative flex w-full items-center justify-center gap-2 rounded-xl bg-dark px-5 py-3.5 text-base font-semibold text-sage shadow-lg transition hover:bg-stone hover:text-dark focus:outline-none focus:ring-2 focus:ring-dark/20 focus:ring-offset-2 focus:ring-offset-white disabled:cursor-not-allowed disabled:opacity-70"
                >
                    <span wire:loading.remove>
                        {{ __('auth_login') }}
                    </span>
                    <span wire:loading class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('auth_connecting') }}
                    </span>
                </button>
            </form>

            <div class="space-y-5">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-ash"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="bg-white px-4 text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">
                            {{ __('auth_continue_with') }}
                        </span>
                    </div>
                </div>

                <div class="grid gap-3">
                    <x-auth-oauth />
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-ash bg-sage px-5 py-4 text-center text-sm text-dark">
            {{ __('auth_no_account') }}
            <x-link :href="route('register')" class="font-semibold text-dark hover:text-stone">
                {{ __('auth_create_account') }}
            </x-link>
        </div>

        <p class="text-center text-xs text-slate-500">
            {{ __('auth_terms_agreement') }}
            <x-link href="#" class="text-dark hover:text-stone">{{ __('auth_terms_of_use') }}</x-link>
            {{ __('auth_and') }}
            <x-link href="#" class="text-dark hover:text-stone">{{ __('auth_privacy_policy') }}</x-link>.
        </p>
    </div>
</x-auth-page>
