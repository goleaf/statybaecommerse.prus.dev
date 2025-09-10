@section('meta')
    <x-meta
        :title="__('Log in') . ' - ' . config('app.name')"
        :description="__('Access your account to track orders, manage addresses, and more')"
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
            <h2 class="font-heading text-2xl font-semibold text-gray-900">
                {{ __('I already have an account') }}
            </h2>
            <p class="sr-only">{{ __('Access your account to track orders, manage addresses, and more') }}</p>
            <div class="my-6 space-y-4">
                <!-- Session Status -->
                <x-auth-session-status :status="session('status')" />

                <form wire:submit="login">
                    {!! $this->getSchema('form')?->toEmbeddedHtml() !!}

                    <div class="space-y-5 mt-6">
                        <x-link class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </x-link>

                        <x-filament::button type="submit" wire:loading.attr="disabled" class="w-full">
                            <span wire:loading.remove>{{ __('Log in') }}</span>
                            <span wire:loading>{{ __('Logging in...') }}</span>
                        </x-filament::button>
                    </div>
                </form>
            </div>

            <x-auth-oauth />
        </div>
        <div class="sm:mx-auto sm:w-full sm:max-w-md py-8">
            <div>
                <h2 class="font-heading text-xl font-semibold text-gray-900">
                    {{ __('New customer') }}
                </h2>
                <p class="mt-3 text-sm/5 text-gray-500">
                    {{ __('Create your own space for an enhanced shopping experience.') }}
                </p>
            </div>
            <div class="mt-6">
                <x-filament::button :href="route('register', ['locale' => app()->getLocale()])" tag="a" color="gray" class="w-full">
                    {{ __('Create account') }}
                </x-filament::button>
            </div>
        </div>
    </div>

    <x-filament-actions::modals />
</div>