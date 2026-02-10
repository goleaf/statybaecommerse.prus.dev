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
            <div class="mb-4 text-sm text-gray-600">
                {{ __('ui.verify_email_prompt') }}
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-4 font-medium text-sm text-green-600">
                    {{ __('ui.a_new_verification_link_has_been_sent_to_the_email_address_you_provided_during_registration') }}
                </div>
            @endif

            <div class="mt-4 flex items-center justify-between">
                <x-buttons.submit
                                  :title="__('ui.resend_verification_email')"
                                  wire:click="sendVerification"
                                  wire:loading.attr="data-loading" />

                <x-buttons.default wire:click="logout" 
                                   wire:confirm="{{ __('translations.confirm_logout') }}"
                                   type="submit">
                    {{ __('ui.log_out') }}
                </x-buttons.default>
            </div>
        </div>
    </div>
</div>
