@section('meta')
    <x-meta
        :title="__('Create account') . ' - ' . config('app.name')"
        :description="__('Create an account to track orders, save favorites, and enjoy a personalized experience')"
        canonical="{{ url()->current() }}" />
@endsection

<!-- Modern Registration Page with Enhanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-100/50 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <!-- Floating geometric shapes -->
        <div class="absolute top-20 left-10 w-32 h-32 bg-blue-400/10 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute top-40 right-20 w-24 h-24 bg-indigo-400/15 rounded-full blur-lg animate-pulse delay-1000"></div>
        <div class="absolute bottom-20 left-1/4 w-40 h-40 bg-purple-400/8 rounded-full blur-2xl animate-pulse delay-2000"></div>
        <div class="absolute bottom-40 right-1/3 w-28 h-28 bg-cyan-400/12 rounded-full blur-xl animate-pulse delay-500"></div>
        
        <!-- Grid pattern overlay -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%239C92AC" fill-opacity="0.03"%3E%3Ccircle cx="30" cy="30" r="1"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-40"></div>
    </div>

    <!-- Main Content Container -->
    <div class="relative z-10 min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
        <div class="w-full max-w-md space-y-8">
            <!-- Header Section -->
            <div class="text-center space-y-4 animate-fade-in-down">
                <!-- Logo/Brand -->
                <div class="flex justify-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Title and Description -->
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold text-gray-900 font-heading">
                        {{ __('Create account') }}
                    </h1>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        {{ __('Join our community and start your journey with us') }}
                    </p>
                </div>
            </div>

            <!-- Registration Form Card -->
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/20 p-8 space-y-6 animate-fade-in-up">
                <!-- Form -->
                <form wire:submit="register" class="space-y-6">
                    <!-- Form Fields Container -->
                    <div class="space-y-5">
                        {!! $this->getSchema('form')?->toEmbeddedHtml() !!}
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold py-3.5 px-6 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none relative overflow-hidden group">
                            
                            <!-- Button Content -->
                            <span class="relative z-10 flex items-center justify-center space-x-2">
                                <span wire:loading.remove class="flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                    </svg>
                                    <span>{{ __('Create account') }}</span>
                                </span>
                                <span wire:loading class="flex items-center space-x-2">
                                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>{{ __('Creating account...') }}</span>
                                </span>
                            </span>
                            
                            <!-- Button Shine Effect -->
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-gray-500 font-medium">{{ __('Or continue with') }}</span>
                    </div>
                </div>

                <!-- OAuth Section -->
                <div class="space-y-3">
                    <x-auth-oauth />
                </div>

                <!-- Login Link -->
                <div class="text-center pt-4">
                    <p class="text-sm text-gray-600">
                        {{ __('Already registered?') }}
                        <x-link 
                            :href="route('login', [])"
                            class="font-semibold text-blue-600 hover:text-blue-700 transition-colors duration-200 ml-1">
                            {{ __('Sign in') }}
                        </x-link>
                    </p>
                </div>
            </div>

            <!-- Terms and Privacy -->
            <div class="text-center animate-fade-in-up" style="animation-delay: 0.2s;">
                <p class="text-xs text-gray-500 leading-relaxed max-w-sm mx-auto">
                    {{ __('By registering to create an account, you agree to our') }}
                    <x-link href="#" class="font-medium text-blue-600 hover:text-blue-700 transition-colors duration-200">
                        {{ __('terms & conditions') }}
                    </x-link>
                    {{ __('and') }}
                    <x-link href="#" class="font-medium text-blue-600 hover:text-blue-700 transition-colors duration-200">
                        {{ __('privacy policy') }}
                    </x-link>.
                </p>
            </div>
        </div>
    </div>
</div>

<x-filament-actions::modals />