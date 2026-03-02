@section('meta')
    <x-meta
        :title="__('auth.ui.login.title') . ' - ' . config('app.name')"
        :description="__('auth.ui.login.subtitle')"
        canonical="{{ url()->current() }}" />
@endsection

<x-auth-page>
    <div class="space-y-2">
        <div class="space-y-2 text-center">
            <h1 class="text-3xl font-bold text-dark sm:text-4xl">
                {{ __('auth.ui.login.welcome_back') }}
            </h1>
            <p class="mx-auto max-w-xl text-sm text-slate-600">
                {{ __('auth.ui.login.welcome_back_subtitle') }}
            </p>
        </div>

        <div class="space-y-2">
            <x-auth-session-status class="text-sm text-dark" :status="session('status')" />

            <form wire:submit="login" class="space-y-6" data-disable-submit-spinner="true">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <x-forms.label for="email" :value="__('messages.auth_email')" />
                        <x-forms.input
                            id="email"
                            type="email"
                            wire:model.defer="loginForm.email"
                            autocomplete="email"
                            autofocus
                            class="rounded-xl border border-ash bg-white px-4 py-3 text-base shadow-sm transition focus:border-dark focus:ring-2 focus:ring-dark/10"
                            placeholder="{{ __('auth.ui.login.email_placeholder') }}"
                        />
                        <x-forms.errors :messages="$errors->get('loginForm.email')" class="mt-1" />
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <x-forms.label for="password" :value="__('messages.auth_password')" />
                            <x-link :href="route('password.request')" class="text-sm font-semibold text-dark hover:text-stone">
                                {{ __('messages.auth_forgot_password') }}
                            </x-link>
                        </div>
                            <x-forms.input
                                id="password"
                                type="password"
                                wire:model.defer="loginForm.password"
                                autocomplete="current-password"
                                class="rounded-xl border border-ash bg-white px-4 py-3 text-base shadow-sm transition focus:border-dark focus:ring-2 focus:ring-dark/10"
                                placeholder="{{ __('auth.ui.login.password_placeholder') }}"
                            />
                        <x-forms.errors :messages="$errors->get('loginForm.password')" class="mt-1" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <label class="inline-flex items-center gap-3 text-sm text-slate-600">
                        <input
                            type="checkbox"
                            wire:model.defer="loginForm.remember"
                            class="h-4 w-4 bg-transparent text-dark focus:ring-dark"
                        >
                        <span>{{ __('messages.auth_remember_me') }}</span>
                    </label>

                    <span class="text-xs text-slate-500">{{ __('auth.ui.login.secure_notice') }}</span>
                </div>

                <button
                    type="submit"
                    class="auth-submit-button w-full bg-dark px-4 py-2.5 text-sm font-semibold focus:outline-none"
                >
                    {{ __('auth.ui.login.title') }}
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-dark">
            {{ __('auth.ui.login.no_account') }}
            <x-link :href="route('register')" class="font-semibold text-dark hover:text-stone">
                {{ __('auth.ui.login.create_account') }}
            </x-link>
        </p>

        <p class="text-center text-xs text-slate-500">
            {{ __('auth.ui.common.terms_agreement') }}
            <x-link href="{{ route('frontend.legal.terms') }}" class="text-dark hover:text-stone">{{ __('auth.ui.common.terms_of_use') }}</x-link>
            {{ __('auth.ui.common.and') }}
            <x-link href="#" class="text-dark hover:text-stone">{{ __('auth.ui.common.privacy_policy') }}</x-link>.
        </p>
    </div>
</x-auth-page>
