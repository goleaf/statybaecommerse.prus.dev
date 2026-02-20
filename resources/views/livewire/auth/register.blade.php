@section('meta')
    <x-meta
        :title="__('auth.ui.register.title') . ' - ' . config('app.name')"
        :description="__('auth.ui.register.subtitle')"
        canonical="{{ url()->current() }}" />
@endsection

<x-auth-page>
    <div class="space-y-10">
        <div class="space-y-4 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-dark text-sage shadow-lg">
                <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>

            <div class="space-y-3">
                <span class="inline-flex items-center gap-2 rounded-full bg-dark/5 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-dark">
                    {{ __('auth.ui.register.join_statbae') }}
                </span>
                <h1 class="text-3xl font-extrabold tracking-tight text-dark sm:text-4xl">
                    {{ __('auth.ui.register.title') }}
                </h1>
                <p class="mx-auto max-w-xl text-sm text-slate-600">
                    {{ __('auth.ui.register.subtitle') }}
                </p>
            </div>
        </div>

        <div class="rounded-2xl border border-ash bg-white p-6 shadow-xl">
        <form wire:submit="register" class="space-y-7">
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                        <x-forms.label for="first_name" :value="__('auth.ui.register.first_name')" />
                    <x-forms.input
                        id="first_name"
                        type="text"
                            wire:model.defer="registrationForm.first_name"
                        autocomplete="given-name"
                            class="rounded-xl border border-ash bg-white px-4 py-3 text-base shadow-sm transition focus:border-dark focus:ring-2 focus:ring-dark/10"
                            placeholder="{{ __('auth.ui.register.first_name_placeholder') }}"
                    />
                    <x-forms.errors :messages="$errors->get('registrationForm.first_name')" class="mt-1" />
                </div>
                <div class="space-y-2">
                        <x-forms.label for="last_name" :value="__('auth.ui.register.last_name')" />
                    <x-forms.input
                        id="last_name"
                        type="text"
                            wire:model.defer="registrationForm.last_name"
                        autocomplete="family-name"
                            class="rounded-xl border border-ash bg-white px-4 py-3 text-base shadow-sm transition focus:border-dark focus:ring-2 focus:ring-dark/10"
                            placeholder="{{ __('auth.ui.register.last_name_placeholder') }}"
                    />
                    <x-forms.errors :messages="$errors->get('registrationForm.last_name')" class="mt-1" />
                </div>
            </div>

            <div class="space-y-2">
                    <x-forms.label for="email" :value="__('messages.auth_email')" />
                <x-forms.input
                    id="email"
                    type="email"
                        wire:model.defer="registrationForm.email"
                    autocomplete="email"
                        class="rounded-xl border border-ash bg-white px-4 py-3 text-base shadow-sm transition focus:border-dark focus:ring-2 focus:ring-dark/10"
                        placeholder="{{ __('auth.ui.login.email_placeholder') }}"
                />
                <x-forms.errors :messages="$errors->get('registrationForm.email')" class="mt-1" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                        <x-forms.label for="password" :value="__('messages.auth_password')" />
                    <x-forms.input
                        id="password"
                        type="password"
                            wire:model.defer="registrationForm.password"
                        autocomplete="new-password"
                            class="rounded-xl border border-ash bg-white px-4 py-3 text-base shadow-sm transition focus:border-dark focus:ring-2 focus:ring-dark/10"
                            placeholder="{{ __('auth.ui.login.password_placeholder') }}"
                    />
                    <x-forms.errors :messages="$errors->get('registrationForm.password')" class="mt-1" />
                        <p class="text-xs text-slate-500">
                            {{ __('auth.ui.register.password_requirements') }}
                    </p>
                </div>

                <div class="space-y-2">
                        <x-forms.label for="password_confirmation" :value="__('auth.ui.register.password_confirm')" />
                    <x-forms.input
                        id="password_confirmation"
                        type="password"
                            wire:model.defer="registrationForm.password_confirmation"
                        autocomplete="new-password"
                            class="rounded-xl border border-ash bg-white px-4 py-3 text-base shadow-sm transition focus:border-dark focus:ring-2 focus:ring-dark/10"
                            placeholder="{{ __('auth.ui.login.password_placeholder') }}"
                    />
                    <x-forms.errors :messages="$errors->get('registrationForm.password_confirmation')" class="mt-1" />
                </div>
            </div>

                <div class="flex items-start gap-3 rounded-2xl border border-ash bg-sage px-4 py-3 text-xs text-dark">
                    <svg class="h-5 w-5 text-dark" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.104 0 2-.896 2-2s-.896-2-2-2-2 .896-2 2 .896 2 2 2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11c0 5-7 10-7 10s-7-5-7-10a7 7 0 1114 0z" />
                </svg>
                <p>
                        {{ __('auth.ui.register.security_notice') }}
                </p>
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                    class="group relative flex w-full items-center justify-center gap-2 rounded-xl bg-dark px-5 py-3.5 text-base font-semibold text-sage shadow-lg transition hover:bg-stone hover:text-dark focus:outline-none focus:ring-2 focus:ring-dark/20 focus:ring-offset-2 focus:ring-offset-white disabled:cursor-not-allowed disabled:opacity-70"
            >
                <span wire:loading.remove>
                        {{ __('auth.ui.register.title') }}
                </span>
                <span wire:loading class="inline-flex items-center gap-2">
                    <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                        {{ __('auth.ui.register.creating_account') }}
                </span>
            </button>
        </form>
        </div>

        <div class="rounded-2xl border border-ash bg-sage px-5 py-4 text-center text-sm text-dark">
            {{ __('auth.ui.login.already_have_account') }}
            <x-link :href="route('login')" class="font-semibold text-dark hover:text-stone">
                {{ __('auth.ui.login.title') }}
            </x-link>
        </div>

        <p class="text-center text-xs text-slate-500">
            {{ __('auth.ui.register.terms_agreement') }}
            <x-link href="#" class="text-dark hover:text-stone">{{ __('auth.ui.common.terms_of_use') }}</x-link>
            {{ __('auth.ui.common.and') }}
            <x-link href="#" class="text-dark hover:text-stone">{{ __('auth.ui.common.privacy_policy') }}</x-link>.
        </p>
    </div>
</x-auth-page>
