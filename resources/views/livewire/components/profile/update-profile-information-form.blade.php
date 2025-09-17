<div>
    <form wire:submit="updateProfileInformation" class="space-y-6">
        <div>
            <x-forms.label for="name" :value="__('Name')" />
            <x-forms.input id="name" type="text" class="mt-1 block w-full" wire:model="name" required autofocus
                           autocomplete="name" />
            <x-forms.errors class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-forms.label for="email" :value="__('Email')" />
            <x-forms.input id="email" type="email" class="mt-1 block w-full" wire:model="email" required
                           autocomplete="username" />
            <x-forms.errors class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">{{ __('Save') }}</button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-gray-600">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</div>
