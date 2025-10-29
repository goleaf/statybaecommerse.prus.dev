{{-- Two-factor challenge screen that mirrors the login layout while collecting OTPs. --}}
@section('meta')
    <x-meta
        :title="__('auth.two_factor.title') . ' - ' . config('app.name')"
        :description="__('auth.two_factor.meta_description')"
        canonical="{{ url()->current() }}" />
@endsection

<x-auth-page>
    <x-slot:aside>
        <div class="flex h-full flex-col justify-between text-white">
            <div class="space-y-10">
                <div class="space-y-5">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-white/70">
                        {{ __('auth.two_factor.badge') }}
                    </span>

                    <h2 class="text-4xl font-semibold leading-tight">
                        {{ __('auth.two_factor.aside_title') }}
                    </h2>

                    <p class="text-sm leading-relaxed text-white/75">
                        {{ __('auth.two_factor.aside_copy') }}
                    </p>
                </div>

                <div class="rounded-3xl border border-white/20 bg-white/10 p-6 shadow-2xl shadow-indigo-900/20 backdrop-blur">
                    <p class="text-sm uppercase tracking-[0.3em] text-white/70">{{ __('auth.two_factor.tip_heading') }}</p>
                    <p class="mt-3 text-base text-white/80">
                        {{ __('auth.two_factor.tip_copy') }}
                    </p>
                </div>
            </div>

            <div class="mt-12 space-y-4 rounded-3xl border border-white/20 bg-gradient-to-br from-white/20 via-white/5 to-transparent p-6 shadow-2xl shadow-indigo-900/30 backdrop-blur">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white/15">
                        <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 22a10 10 0 100-20 10 10 0 000 20z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ __('auth.two_factor.aside_quote_title') }}</p>
                        <p class="text-xs text-white/70">{{ __('auth.two_factor.aside_quote_byline') }}</p>
                    </div>
                </div>
                <p class="text-sm text-white/75">
                    {{ __('auth.two_factor.aside_quote_copy') }}
                </p>
            </div>
        </div>
    </x-slot:aside>

    <div class="space-y-12">
        <div class="space-y-4 text-center">
            <span class="mx-auto inline-flex items-center gap-2 rounded-full bg-slate-900/5 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">
                {{ __('auth.two_factor.badge_secondary') }}
            </span>

            <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                {{ __('auth.two_factor.title') }}
            </h1>

            <p class="mx-auto max-w-xl text-base text-slate-600">
                {{ __('auth.two_factor.subtitle', ['identifier' => $maskedIdentifier !== '' ? $maskedIdentifier : __('auth.two_factor.default_identifier')]) }}
            </p>
        </div>

        <div class="space-y-8">
            <div class="rounded-2xl border border-slate-200/70 bg-slate-50 px-5 py-3 text-xs font-semibold text-slate-600">
                <div class="flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 5.25h4.5v2.5h-4.5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 8.25h12M6 12h12M6 15.75h12" />
                    </svg>
                    <span>{{ __('auth.two_factor.notice') }}</span>
                </div>
            </div>

            <form wire:submit="verify" class="space-y-7">
                <div class="space-y-6">
                    <div class="space-y-2">
                        <x-forms.label for="two-factor-code" :value="__('auth.two_factor.code_label')" />
                        <x-forms.input
                            id="two-factor-code"
                            type="text"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            maxlength="6"
                            wire:model.defer="code"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base tracking-[0.4em] text-center uppercase shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                            placeholder="123 456"
                        />
                        <x-forms.errors :messages="$errors->get('code')" class="mt-1" />
                    </div>

                    <div class="space-y-2">
                        <x-forms.label for="recovery-code" :value="__('auth.two_factor.recovery_label')" />
                        <x-forms.input
                            id="recovery-code"
                            type="text"
                            autocomplete="one-time-code"
                            wire:model.defer="recoveryCode"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                            placeholder="A1B2C3D4"
                        />
                        <p class="text-xs text-slate-500">{{ __('auth.two_factor.recovery_help') }}</p>
                        <x-forms.errors :messages="$errors->get('recoveryCode')" class="mt-1" />
                    </div>
                </div>

                <div class="space-y-3">
                    {{-- Use the shared buttons.primary component so the template resolves without missing aliases. --}}
                    <x-buttons.primary type="submit" class="w-full justify-center rounded-2xl px-5 py-3 text-base font-semibold">
                        {{ __('auth.two_factor.submit') }}
                    </x-buttons.primary>
                    <p class="text-center text-xs text-slate-500">
                        {{ __('auth.two_factor.security_footer') }}
                    </p>
                </div>
            </form>
        </div>
    </div>
</x-auth-page>
