@section('meta')
    <x-meta
        :title="__('Create account') . ' - ' . config('app.name')"
        :description="__('Create an account to track orders, save favorites, and enjoy a personalized experience')"
        canonical="{{ url()->current() }}" />
@endsection

<x-auth-page>
    <x-slot:aside>
        <div class="flex h-full flex-col justify-between">
            <div class="space-y-8">
                <div class="space-y-4">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white/70">
                        {{ __('Why join us') }}
                    </span>

                    <h2 class="text-3xl font-semibold leading-tight text-white">
                        {{ __('Unlock curated experiences and faster shopping with Statybae Commerce') }}
                    </h2>

                    <p class="text-sm leading-relaxed text-white/75">
                        {{ __('Create a free account to receive exclusive drops, personalised recommendations, and checkout in a fraction of the time on every visit.') }}
                    </p>
                </div>

                <div class="space-y-6">
                    <div class="rounded-3xl border border-white/15 bg-white/5 p-6">
                        <p class="text-sm text-white/80">
                            <span class="block text-xs uppercase tracking-[0.18em] text-white/60">{{ __('Member spotlight') }}</span>
                            <span class="mt-3 block text-base font-medium text-white">{{ __('“I reorder essentials in seconds and always know what’s arriving.”') }}</span>
                            <span class="mt-2 block text-xs text-white/60">{{ __('Elena, premium member since 2021') }}</span>
                        </p>
                    </div>

                    <ul class="space-y-4 text-sm text-white/80">
                        <li class="flex items-start gap-3">
                            <div class="mt-1 flex h-7 w-7 items-center justify-center rounded-full bg-white/15">
                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span>{{ __('Earn points and surprise rewards every time you shop.') }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="mt-1 flex h-7 w-7 items-center justify-center rounded-full bg-white/15">
                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 2" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 22a10 10 0 100-20 10 10 0 000 20z" />
                                </svg>
                            </div>
                            <span>{{ __('Save multiple addresses and payment preferences for instant checkout.') }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="mt-1 flex h-7 w-7 items-center justify-center rounded-full bg-white/15">
                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m6 14h2a2 2 0 002-2V7a2 2 0 00-2-2h-2" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9h12M7 21H5a2 2 0 01-2-2V9" />
                                </svg>
                            </div>
                            <span>{{ __('Keep order history, invoices, and returns tidy within your dashboard.') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 rounded-2xl border border-white/20 bg-white/5 p-6">
                <p class="text-sm text-white/80">
                    {{ __('Already have an account?') }}
                    <x-link :href="route('login')" class="ml-1 font-semibold text-white hover:text-white/90">
                        {{ __('Sign in instead') }}
                    </x-link>
                </p>
            </div>
        </div>
    </x-slot:aside>

    <div class="space-y-10">
        <div class="space-y-4 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-400 to-blue-500 shadow-lg">
                <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>

            <div class="space-y-2">
                <h1 class="text-3xl font-bold text-slate-900 sm:text-4xl">
                    {{ __('Create your account') }}
                </h1>
                <p class="text-base text-slate-600">
                    {{ __('Join our community and make every order easier, smarter, and more rewarding.') }}
                </p>
            </div>
        </div>

        <form wire:submit="register" class="space-y-7">
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <x-forms.label for="first_name" :value="__('First name')" />
                    <x-forms.input
                        id="first_name"
                        type="text"
                        wire:model.defer="registrationForm.first_name"
                        autocomplete="given-name"
                        class="rounded-xl border border-slate-200 bg-white/90 px-4 py-3 text-base shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                        placeholder="{{ __('Jane') }}"
                    />
                    <x-forms.errors :messages="$errors->get('registrationForm.first_name')" class="mt-1" />
                </div>

                <div class="space-y-2">
                    <x-forms.label for="last_name" :value="__('Last name')" />
                    <x-forms.input
                        id="last_name"
                        type="text"
                        wire:model.defer="registrationForm.last_name"
                        autocomplete="family-name"
                        class="rounded-xl border border-slate-200 bg-white/90 px-4 py-3 text-base shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                        placeholder="{{ __('Doe') }}"
                    />
                    <x-forms.errors :messages="$errors->get('registrationForm.last_name')" class="mt-1" />
                </div>
            </div>

            <div class="space-y-2">
                <x-forms.label for="email" :value="__('Email address')" />
                <x-forms.input
                    id="email"
                    type="email"
                    wire:model.defer="registrationForm.email"
                    autocomplete="email"
                    class="rounded-xl border border-slate-200 bg-white/90 px-4 py-3 text-base shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                    placeholder="{{ __('you@example.com') }}"
                />
                <x-forms.errors :messages="$errors->get('registrationForm.email')" class="mt-1" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <x-forms.label for="password" :value="__('Password')" />
                    <x-forms.input
                        id="password"
                        type="password"
                        wire:model.defer="registrationForm.password"
                        autocomplete="new-password"
                        class="rounded-xl border border-slate-200 bg-white/90 px-4 py-3 text-base shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                        placeholder="••••••••"
                    />
                    <x-forms.errors :messages="$errors->get('registrationForm.password')" class="mt-1" />
                    <p class="text-xs text-slate-400">
                        {{ __('Use at least 8 characters with a mix of letters, numbers & symbols.') }}
                    </p>
                </div>

                <div class="space-y-2">
                    <x-forms.label for="password_confirmation" :value="__('Confirm password')" />
                    <x-forms.input
                        id="password_confirmation"
                        type="password"
                        wire:model.defer="registrationForm.password_confirmation"
                        autocomplete="new-password"
                        class="rounded-xl border border-slate-200 bg-white/90 px-4 py-3 text-base shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                        placeholder="••••••••"
                    />
                    <x-forms.errors :messages="$errors->get('registrationForm.password_confirmation')" class="mt-1" />
                </div>
            </div>

            <div class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-3 text-xs text-slate-500">
                <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.104 0 2-.896 2-2s-.896-2-2-2-2 .896-2 2 .896 2 2 2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11c0 5-7 10-7 10s-7-5-7-10a7 7 0 1114 0z" />
                </svg>
                <p>
                    {{ __('We safeguard your personal data with enterprise-grade security and never share it without consent.') }}
                </p>
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="group relative flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 via-indigo-500 to-blue-500 px-5 py-3.5 text-base font-semibold text-white shadow-lg transition hover:from-indigo-600 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-indigo-400/40 focus:ring-offset-2 focus:ring-offset-white disabled:cursor-not-allowed disabled:opacity-70"
            >
                <span wire:loading.remove>
                    {{ __('Create account') }}
                </span>
                <span wire:loading class="inline-flex items-center gap-2">
                    <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('Creating account...') }}
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
                        {{ __('Or join with') }}
                    </span>
                </div>
            </div>

            <div class="grid gap-3">
                <x-auth-oauth />
            </div>
        </div>

        <p class="text-center text-xs text-slate-400">
            {{ __('By creating an account you agree to our') }}
            <x-link href="#" class="text-indigo-500 hover:text-indigo-600">{{ __('Terms of Service') }}</x-link>
            {{ __('and') }}
            <x-link href="#" class="text-indigo-500 hover:text-indigo-600">{{ __('Privacy Policy') }}</x-link>.
        </p>
    </div>
</x-auth-page>
