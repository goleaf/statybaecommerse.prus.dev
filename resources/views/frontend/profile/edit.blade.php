<x-layouts.base title="{{ __('messages.frontend) }}">
    <div class="max-w-3xl mx-auto px-4 py-10 space-y-6">
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.frontend) }}</h1>
        <form method="POST" action="{{ route('frontend.profile.update') }}" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="name">{{ __('messages.frontend) }}</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                @error('name')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="email">{{ __('frontend.profile.fields.email_label') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                @error('email')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="password">{{ __('messages.frontend) }}</label>
                <input id="password" name="password" type="password" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800" autocomplete="new-password">
                @error('password')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="password_confirmation">{{ __('frontend.profile.fields.password_confirmation') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800" autocomplete="new-password">
            </div>
            <div class="flex justify-end gap-4">
                <a href="{{ route('frontend.profile.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">{{ __('messages.frontend) }}</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">{{ __('messages.frontend) }}</button>
            </div>
        </form>
    </div>
</x-layouts.base>
