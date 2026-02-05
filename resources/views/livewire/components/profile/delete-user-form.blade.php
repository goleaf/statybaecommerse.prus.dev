<div>
    @if (!$confirmUserDeletion)
        <div class="max-w-xl text-sm text-gray-600">
            <p>{{ __('ui.once_your_account_is_deleted_all_of_its_resources_and_data_will_be_permanently_deleted_before_deleting_your_account_please_download_any_data_or_information_that_you_wish_to_retain') }}
            </p>
        </div>

        <div class="mt-5">
            <button type="button" wire:click="confirmUserDeletion" wire:loading.attr="disabled"
                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                {{ __('ui.delete_account') }}
            </button>
        </div>
    @else
        <div class="max-w-xl text-sm text-gray-600">
            <p>{{ __('ui.are_you_sure_you_want_to_delete_your_account_once_your_account_is_deleted_all_of_its_resources_and_data_will_be_permanently_deleted_please_enter_your_password_to_confirm_you_would_like_to_permanently_delete_your_account') }}
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
                    {{ __('messages.cancel') }}
                </button>

                <button type="button" wire:click="deleteUser" 
                        wire:confirm.prompt="{{ __('translations.confirm_delete_account') }}\n\n{{ __('translations.confirm_type_delete') }}|DELETE"
                        wire:loading.attr="disabled"
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    {{ __('ui.delete_account') }}
                </button>
            </div>
        </form>
    @endif
</div>
