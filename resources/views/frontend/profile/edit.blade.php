<x-frontend.profile-layout title="{{ __('frontend.profile.edit_title') }}">
    <div class="space-y-6">
        <header class="border-b border-gray-200 dark:border-white/10 pb-5 mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('frontend.profile.edit_title') }}</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Update your personal information and password.</p>
        </header>

        <form method="POST" action="{{ route('frontend.profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-6 border border-gray-100 dark:border-white/5 space-y-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="name">{{ __('messages.Name') }}</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" required 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="John Doe">
                    @error('name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="email">{{ __('frontend.profile.fields.email_label') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="john@example.com">
                    @error('email')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-6 border border-gray-100 dark:border-white/5 space-y-6">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Change Password</h3>
                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="password">{{ __('messages.Password') }} <span class="text-xs text-gray-500 font-normal">(Leave blank to keep current)</span></label>
                    <input id="password" name="password" type="password" 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                        autocomplete="new-password">
                    @error('password')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Password Confirmation -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="password_confirmation">{{ __('frontend.profile.fields.password_confirmation') }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                        autocomplete="new-password">
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-white/10">
                <a href="{{ route('frontend.profile.index') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                    {{ __('messages.Cancel') }}
                </a>
                <button type="submit" class="inline-flex justify-center items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                    {{ __('messages.Save') }}
                </button>
            </div>
        </form>
    </div>
</x-frontend.profile-layout>
