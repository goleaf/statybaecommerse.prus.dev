@section('meta')
    <x-meta
        :title="__('Log in') . ' - ' . config('app.name')"
        :description="__('Access your account to track orders, manage addresses, and more')"
        canonical="{{ url()->current() }}" />
@endsection

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 login-container">
    <!-- Background Pattern -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-indigo-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-tr from-purple-400/20 to-pink-600/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
    </div>

    <div class="relative max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center login-header">
            <div class="mx-auto h-16 w-16 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg hover:scale-110 transition-transform duration-300">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <h2 class="mt-6 text-3xl font-bold text-gray-900">
                {{ __('Welcome back') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                {{ __('Sign in to your account to continue') }}
            </p>
        </div>

        <!-- Login Form Card -->
        <div class="login-card rounded-2xl shadow-xl p-8 login-form">
            <!-- Session Status -->
            <x-auth-session-status :status="session('status')" />

            <form wire:submit="login" class="space-y-6">
                {!! $this->getSchema('form')?->toEmbeddedHtml() !!}

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                            {{ __('Remember me') }}
                        </label>
                    </div>

                    <div class="text-sm">
                        <x-link class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-200" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </x-link>
                    </div>
                </div>

                <div>
                    <button type="submit" wire:loading.attr="disabled" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg wire:loading.remove wire:target="login" class="h-5 w-5 text-blue-500 group-hover:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                            </svg>
                            <svg wire:loading wire:target="login" class="animate-spin h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove wire:target="login">{{ __('Sign in') }}</span>
                        <span wire:loading wire:target="login">{{ __('Signing in...') }}</span>
                    </button>
                </div>
            </form>

            <!-- OAuth Section -->
            <div class="mt-8 login-oauth">
                <x-auth-oauth />
            </div>

            <!-- Register Link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    {{ __("Don't have an account?") }}
                    <x-link class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-200" href="{{ route('register') }}">
                        {{ __('Sign up') }}
                    </x-link>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center login-footer">
            <p class="text-xs text-gray-500">
                {{ __('By signing in, you agree to our') }}
                <x-link class="text-blue-600 hover:text-blue-500" href="#">{{ __('Terms of Service') }}</x-link>
                {{ __('and') }}
                <x-link class="text-blue-600 hover:text-blue-500" href="#">{{ __('Privacy Policy') }}</x-link>
            </p>
        </div>
    </div>

    <x-filament-actions::modals />
</div>