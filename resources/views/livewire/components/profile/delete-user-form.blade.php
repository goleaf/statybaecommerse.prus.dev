<div>
    @if (!$confirmUserDeletion)
        <div class="max-w-xl text-sm text-gray-600">
            <p>{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
            </p>
        </div>

        <div class="mt-5">
            <button type="button" wire:click="confirmUserDeletion" wire:loading.attr="disabled"
                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                {{ __('Delete Account') }}
            </button>
        </div>
    @else
        <div class="max-w-xl text-sm text-gray-600">
            <p>{{ __('Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>
        </div>

        <form wire:submit="deleteUser" class="mt-6 space-y-6">
            <div>
                <x-forms.label for="password" :value="__('Password')" />
                <x-forms.input id="password" type="password" class="mt-1 block w-full" wire:model="password" required
                               autocomplete="current-password" />
                <x-forms.errors class="mt-2" :messages="$errors->get('password')" />
            </div>

            <div class="flex items-center gap-4">
                <button type="button" wire:click="$set('confirmUserDeletion', false)" wire:loading.attr="disabled"
                        class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    {{ __('Cancel') }}
                </button>

                <button type="button" wire:click="deleteUser" wire:loading.attr="disabled"
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    @endif
</div>
