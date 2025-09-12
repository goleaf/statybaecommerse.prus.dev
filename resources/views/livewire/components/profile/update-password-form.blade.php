<div>
    <form wire:submit="updatePassword" class="space-y-6">
        <div>
            <x-forms.label for="current_password" :value="__('Current Password')" />
            <x-forms.input id="current_password" type="password" class="mt-1 block w-full" wire:model="current_password"
                           required autocomplete="current-password" />
            <x-forms.errors class="mt-2" :messages="$errors->get('current_password')" />
        </div>

        <div>
            <x-forms.label for="password" :value="__('New Password')" />
            <x-forms.input id="password" type="password" class="mt-1 block w-full" wire:model="password" required
                           autocomplete="new-password" />
            <x-forms.errors class="mt-2" :messages="$errors->get('password')" />
        </div>

        <div>
            <x-forms.label for="password_confirmation" :value="__('Confirm Password')" />
            <x-forms.input id="password_confirmation" type="password" class="mt-1 block w-full"
                           wire:model="password_confirmation" required autocomplete="new-password" />
            <x-forms.errors class="mt-2" :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">{{ __('Save') }}</button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-gray-600">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</div>
