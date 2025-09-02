<?php
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $preferred_locale = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->first_name = Auth::user()->first_name;
        $this->last_name = Auth::user()->last_name;
        $this->email = Auth::user()->email;
        $this->preferred_locale = (string) (Auth::user()->preferred_locale ?: app()->getLocale());
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $supportedConfig = config('app.supported_locales', 'en');
        $supported = collect(is_array($supportedConfig) ? $supportedConfig : explode(',', (string) $supportedConfig))
            ->map(fn($v) => trim($v))
            ->filter()
            ->values()
            ->all();

        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'preferred_locale' => ['nullable', 'string', Rule::in($supported)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Apply locale preference immediately
        if (!empty($this->preferred_locale)) {
            app()->setLocale($this->preferred_locale);
            session(['app.locale' => $this->preferred_locale]);
            cookie()->queue(cookie('app_locale', $this->preferred_locale, 60 * 24 * 30));
        }

        $this->dispatch('profile-updated', name: $user->full_name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('account'));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="pb-10">
    <header>
        <h2 class="text-lg font-medium text-gray-900 lg:text-xl">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-2 text-sm text-gray-500">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6 max-w-xl">
        <div>
            <x-forms.label for="first_name" :value="__('First Name')" />
            <x-forms.input wire:model="first_name" id="first_name" name="first_name" type="text"
                           class="block w-full mt-1" required autofocus autocomplete="first-name" />
            <x-forms.errors class="mt-2" :messages="$errors->get('first_name')" />
        </div>

        <div>
            <x-forms.label for="preferred_locale" :value="__('Preferred language')" />
            @php
                $supported = config('app.supported_locales', ['en']);
                $locales = collect(is_array($supported) ? $supported : explode(',', (string) $supported))
                    ->map(fn($v) => trim($v))
                    ->filter()
                    ->values();
            @endphp
            <select id="preferred_locale" name="preferred_locale" wire:model="preferred_locale"
                    class="block w-full mt-1 border-gray-300 rounded-md">
                <option value="">{{ __('System default') }}</option>
                @foreach ($locales as $loc)
                    <option value="{{ $loc }}">{{ strtoupper($loc) }}</option>
                @endforeach
            </select>
            <x-forms.errors class="mt-2" :messages="$errors->get('preferred_locale')" />
            <p class="mt-2 text-xs text-gray-500">{{ __('Used for emails and default site language.') }}</p>
        </div>

        <div>
            <x-forms.label for="last_name" :value="__('Last Name')" />
            <x-forms.input wire:model="last_name" id="last_name" name="last_name" type="text"
                           class="block w-full mt-1" required autofocus autocomplete="last-name" />
            <x-forms.errors class="mt-2" :messages="$errors->get('last_name')" />
        </div>

        <div>
            <x-forms.label for="email" :value="__('Email')" />
            <x-forms.input wire:model="email" id="email" name="email" type="email" class="block w-full mt-1"
                           required autocomplete="username" />
            <x-forms.errors class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button wire:click.prevent="sendVerification"
                                class="text-sm text-gray-600 underline rounded-md hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-buttons.submit :title="__('Save')" wire:loading.attr="data-loading" />
        </div>
    </form>
</section>
