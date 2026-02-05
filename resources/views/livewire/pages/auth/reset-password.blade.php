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
            <h2 class="text-xl font-semibold text-gray-900 font-heading">
                {{ __('ui.reset_your_password') }}
            </h2>

            <form wire:submit="resetPassword" class="mt-6 space-y-4">
                <!-- Email Address -->
                <div>
                    <x-forms.label for="email" :value="__('messages.email')" />
                    <x-forms.input wire:model="email" id="email" class="block mt-1 w-full" type="email"
                                   name="email" required autofocus autocomplete="username" />
                    <x-forms.errors :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <x-forms.label for="password" :value="__('Password')" />
                    <x-forms.input wire:model="password" id="password" class="block mt-1 w-full" type="password"
                                   name="password" required autocomplete="new-password" />
                    <x-forms.errors :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <x-forms.label for="password_confirmation" :value="__('ui.confirm_password')" />
                    <x-forms.input
                                   wire:model="password_confirmation"
                                   id="password_confirmation"
                                   class="block mt-1 w-full"
                                   type="password"
                                   name="password_confirmation"
                                   autocomplete="new-password"
                                   required />

                    <x-forms.errors :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end">
                    <x-buttons.submit :title="__('ui.reset_password')" wire:loading.attr="data-loading" />
                </div>
            </form>
        </div>
    </div>
</div>