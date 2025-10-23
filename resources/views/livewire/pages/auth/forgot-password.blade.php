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
                <h1 class="text-3xl font-semibold text-slate-900 dark:text-slate-50">{{ __('Forgot password') }}</h1>
                <p class="text-slate-600 dark:text-slate-300">
                    {{ __('Enter your email address and we will send you a secure password reset link.') }}
                </p>
            </div>
        </div>

        @if (session('status'))
            <x-alert icon="heroicon-o-check-circle" state="success">
                <span>{{ session('status') }}</span>
            </x-alert>
        @endif

        <form wire:submit.prevent="sendPasswordResetLink" class="grid gap-6">
            <div class="grid gap-2">
                <x-forms.label for="email" :value="__('Email address')" />
                <x-forms.input id="email" type="email" wire:model.defer="email" autocomplete="email" required />
                <x-forms.errors :messages="$errors->get('email')" class="mt-1" />
            </div>

            @if ($captchaQuestion)
                <div class="grid gap-2">
                    <x-forms.label for="captcha-response" :value="$captchaQuestion" />
                    <div class="flex gap-2">
                        <x-forms.input id="captcha-response" type="text" wire:model.defer="captchaResponse" required />
                        <x-buttons.secondary type="button" wire:click="refreshCaptcha" wire:loading.attr="disabled">
                            {{ __('Refresh') }}
                        </x-buttons.secondary>
                    </div>
                    {{-- Keep the CAPTCHA token in sync so the backend can validate the answer reliably. --}}
                    <input type="hidden" wire:model="captchaToken" />
                    <x-forms.errors :messages="$errors->get('captchaResponse')" class="mt-1" />
                </div>
            @endif

            <div class="flex flex-col gap-4">
                <x-buttons.submit :title="__('Send reset link')" wire:loading.attr="data-loading" class="w-full" />
                <a class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-300" href="{{ route('login') }}">
                    {{ __('Back to sign in') }}
                </a>
            </div>
        </form>
    </div>
</x-auth-page>
