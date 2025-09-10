@section('meta')
    <x-meta
        :title="__('Create account') . ' - ' . config('app.name')"
        :description="__('Create an account to track orders, save favorites, and enjoy a personalized experience')"
        canonical="{{ url()->current() }}" />
@endsection

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

    <div class="relative min-h-full flex flex-col justify-center py-12 divide-y divide-gray-200 lg:max-w-2xl lg:mx-auto">
        <div class="sm:mx-auto sm:w-full sm:max-w-md py-8">
            <h2 class="text-xl font-semibold text-gray-900 font-heading">
                {{ __('Create account') }}
            </h2>
            <div class="mt-6 space-y-6">
                <form wire:submit="register">
                    {!! $this->getSchema('form')?->toEmbeddedHtml() !!}

                    <div class="mt-6 space-y-2">
                        <x-link
                                class="underline text-sm text-gray-500 hover:text-gray-900"
                                :href="route('login', ['locale' => app()->getLocale()])">
                            {{ __('Already registered?') }}
                        </x-link>

                        <x-filament::button type="submit" wire:loading.attr="disabled" class="w-full">
                            <span wire:loading.remove>{{ __('Create account') }}</span>
                            <span wire:loading>{{ __('Creating account...') }}</span>
                        </x-filament::button>
                    </div>
                </form>

                <x-auth-oauth />

                <p class="text-base text-center leading-6 text-gray-500">
                    {{ __('By registering to create an account, you agree to our') }}
                    <x-link href="#" class="font-medium text-gray-900 group group-link-underline">
                        <span class="link link-underline link-underline-black">{{ __('terms & conditions') }}</span>
                    </x-link>.
                    {{ __('Please read our') }}
                    <x-link href="#" class="font-medium text-gray-900 group group-link-underline">
                        <span class="link link-underline link-underline-black">{{ __('privacy policy') }}</span>
                    </x-link>.
                </p>
            </div>
        </div>
    </div>

    <x-filament-actions::modals />
</div>