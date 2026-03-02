<div>
    <form wire:submit="updateProfileInformation" class="space-y-5">
        <div>
            <x-forms.label for="name" :value="__('messages.name')" />
            <x-forms.input id="name" type="text" class="mt-1 block w-full rounded-md border-gray-300" wire:model="name" required autofocus
                           autocomplete="name" />
            <x-forms.errors class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-forms.label for="email" :value="__('messages.email')" />
            <x-forms.input id="email" type="email" class="mt-1 block w-full rounded-md border-gray-300" wire:model="email" required
                           autocomplete="username" />
            <x-forms.errors class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                    class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">{{ __('messages.save') }}</button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-gray-600">
                    {{ __('ui.saved') }}
                </p>
            @endif
        </div>
    </form>
</div>
