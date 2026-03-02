<div>
    <p class="mb-4 text-sm text-gray-600">{{ __('ui.ensure_your_account_is_using_a_long_random_password_to_stay_secure') }}</p>

    <form wire:submit="updatePassword" class="space-y-5">
        <div>
            <x-forms.label for="current_password" :value="__('ui.current_password')" />
            <x-forms.input id="current_password" type="password" class="mt-1 block w-full rounded-md border-gray-300" wire:model="current_password"
                           required autocomplete="current-password" />
            <x-forms.errors class="mt-2" :messages="$errors->get('current_password')" />
        </div>

        <div>
            <x-forms.label for="password" :value="__('ui.new_password')" />
            <x-forms.input id="password" type="password" class="mt-1 block w-full rounded-md border-gray-300" wire:model="password" required
                           autocomplete="new-password" />
            <x-forms.errors class="mt-2" :messages="$errors->get('password')" />
        </div>

        <div>
            <x-forms.label for="password_confirmation" :value="__('ui.confirm_password')" />
            <x-forms.input id="password_confirmation" type="password" class="mt-1 block w-full rounded-md border-gray-300"
                           wire:model="password_confirmation" required autocomplete="new-password" />
            <x-forms.errors class="mt-2" :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                    class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">{{ __('ui.update_password') }}</button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-gray-600">
                    {{ __('ui.saved') }}
                </p>
            @endif
        </div>
    </form>
</div>
