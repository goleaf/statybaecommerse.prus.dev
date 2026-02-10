<div class="relative">
    <svg
         class="absolute inset-0 -z-10 h-full w-full stroke-gray-100 [mask-image:radial-gradient(100%_100%_at_top_right,white,transparent)]"
         aria-hidden="true">
        <defs>
            <pattern
                     id="0787a7c5-978c-4f66-83c7-11c213f99cb7"
                     width="200"
                     height="200"
                     x="50%"
                     y="-1"
                     patternUnits="userSpaceOnUse">
                <path d="M.5 200V.5H200" fill="none" />
            </pattern>
        </defs>
        <rect width="100%" height="100%" stroke-width="0" fill="url(#0787a7c5-978c-4f66-83c7-11c213f99cb7)" />
    </svg>

    <div
         class="relative min-h-full flex flex-col justify-center py-12 divide-y divide-gray-200 lg:max-w-2xl lg:mx-auto">
        <div class="sm:mx-auto sm:w-full sm:max-w-md py-8">
            <div class="text-sm text-gray-500">
                {{ __('ui.this_is_a_secure_area_of_the_application_please_confirm_your_password_before_continuing') }}
            </div>

            <form wire:submit="confirmPassword" class="mt-6">
                <!-- Password -->
                <div>
                    <x-forms.label for="password" :value="__('ui.password')" />
                    <x-forms.input wire:model="password"
                                   id="password"
                                   class="block mt-1 w-full"
                                   type="password"
                                   name="password"
                                   required autocomplete="current-password" />

                    <x-forms.errors :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex justify-end mt-4">
                    <x-buttons.submit :title="__('ui.confirm')" wire:loading.attr="data-loading" />
                </div>
            </form>
        </div>
    </div>
</div>
