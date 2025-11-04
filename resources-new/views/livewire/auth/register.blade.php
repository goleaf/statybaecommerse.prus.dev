@section('meta')
    <x-meta
        :title="__('auth_register_title') . ' - ' . config('app.name')"
        :description="__('auth_register_subtitle')"
        canonical="{{ url()->current() }}" />
@endsection

<x-auth-page>
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
                    {{ __('auth_already_have_account') }}
                    <x-link :href="route('login')" class="ml-1 font-semibold text-dark hover:text-black/80">
                        {{ __('auth_login_link') }}
                    </x-link>
                </p>
            </div>
        </div>
    </x-slot:aside>

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
                    {{ __('auth_join_statbae') }}
                </span>
                <h1 class="text-3xl font-extrabold tracking-tight text-dark sm:text-4xl">
                    {{ __('auth_register_title') }}
                </h1>
                <p class="mx-auto max-w-xl text-sm text-slate-600">
                    {{ __('auth_register_subtitle') }}
                </p>
            </div>
        </div>
        <div class="rounded-2xl border border-ash bg-white p-6 shadow-xl">
            <form wire:submit="register" class="space-y-7">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="space-y-2">
                        <x-forms.label for="first_name" :value="__('auth_first_name')" />
                        <x-forms.input
                            id="first_name"
                            type="text"
                            wire:model.defer="registrationForm.first_name"
                            autocomplete="given-name"
                            class="rounded-xl border border-ash bg-white px-4 py-3 text-base shadow-sm transition focus:border-dark focus:ring-2 focus:ring-dark/10"
                            placeholder="{{ __('auth_first_name_placeholder') }}"
                        />
                        <x-forms.errors :messages="$errors->get('registrationForm.first_name')" class="mt-1" />
                    </div>
                    <div class="space-y-2">
                        <x-forms.label for="last_name" :value="__('auth_last_name')" />
                        <x-forms.input
                            id="last_name"
                            type="text"
                            wire:model.defer="registrationForm.last_name"
                            autocomplete="family-name"
                            class="rounded-xl border border-ash bg-white px-4 py-3 text-base shadow-sm transition focus:border-dark focus:ring-2 focus:ring-dark/10"
                            placeholder="{{ __('auth_last_name_placeholder') }}"
                        />
                        <x-forms.errors :messages="$errors->get('registrationForm.last_name')" class="mt-1" />
                    </div>
                </div>

                <div class="space-y-2">
                    <x-forms.label for="email" :value="__('auth_email')" />
                    <x-forms.input
                        id="email"
                        type="email"
                        wire:model.defer="registrationForm.email"
                        autocomplete="email"
                        class="rounded-xl border border-ash bg-white px-4 py-3 text-base shadow-sm transition focus:border-dark focus:ring-2 focus:ring-dark/10"
                        placeholder="{{ __('auth_email_placeholder') }}"
                    />
                    <x-forms.errors :messages="$errors->get('registrationForm.email')" class="mt-1" />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="space-y-2">
                        <x-forms.label for="password" :value="__('auth_password')" />
                        <x-forms.input
                            id="password"
                            type="password"
                            wire:model.defer="registrationForm.password"
                            autocomplete="new-password"
                            class="rounded-xl border border-ash bg-white px-4 py-3 text-base shadow-sm transition focus:border-dark focus:ring-2 focus:ring-dark/10"
                            placeholder="{{ __('auth_password_placeholder') }}"
                        />
                        <x-forms.errors :messages="$errors->get('registrationForm.password')" class="mt-1" />
                        <p class="text-xs text-slate-500">
                            {{ __('auth_password_requirements') }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <x-forms.label for="password_confirmation" :value="__('auth_password_confirm')" />
                        <x-forms.input
                            id="password_confirmation"
                            type="password"
                            wire:model.defer="registrationForm.password_confirmation"
                            autocomplete="new-password"
                            class="rounded-xl border border-ash bg-white px-4 py-3 text-base shadow-sm transition focus:border-dark focus:ring-2 focus:ring-dark/10"
                            placeholder="{{ __('auth_password_placeholder') }}"
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
                        {{ __('auth_security_notice') }}
                    </p>
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="group relative flex w-full items-center justify-center gap-2 rounded-xl bg-dark px-5 py-3.5 text-base font-semibold text-sage shadow-lg transition hover:bg-stone hover:text-dark focus:outline-none focus:ring-2 focus:ring-dark/20 focus:ring-offset-2 focus:ring-offset-white disabled:cursor-not-allowed disabled:opacity-70"
                >
                    <span wire:loading.remove>
                        {{ __('auth_register') }}
                    </span>
                    <span wire:loading class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('auth_creating_account') }}
                    </span>
                </button>
            </form>
        </div>

        <div class="space-y-5">
            <div class="relative">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-ash"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="bg-white px-4 text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">
                        {{ __('auth_or_login_with') }}
                    </span>
                </div>
            </div>

            <div class="grid gap-3">
                <x-auth-oauth />
            </div>
        </div>

        <p class="text-center text-xs text-slate-500">
            {{ __('auth_terms_agreement_register') }}
            <x-link href="#" class="text-dark hover:text-stone">{{ __('auth_terms_of_use') }}</x-link>
            {{ __('auth_and') }}
            <x-link href="#" class="text-dark hover:text-stone">{{ __('auth_privacy_policy') }}</x-link>.
        </p>
    </div>
</x-auth-page>

