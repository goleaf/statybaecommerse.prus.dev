<x-layouts.base title="{{ __('My profile') }}">
    <div class="max-w-4xl mx-auto px-4 py-10 space-y-8">
        <header class="flex items-center justify-between">
            <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Account overview') }}</h1>
            <a href="{{ route('frontend.profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">{{ __('Edit profile') }}</a>
        </header>

        <section class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-semibold mb-4">{{ __('Personal details') }}</h2>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-300">
                <div>
                    <dt class="font-medium text-gray-900 dark:text-gray-100">{{ __('Name') }}</dt>
                    <dd>{{ $user->name }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-900 dark:text-gray-100">{{ __('Email') }}</dt>
                    <dd>{{ $user->email }}</dd>
                </div>
            </dl>
        </section>

        <section class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold">{{ __('Saved addresses') }}</h2>
                <a href="{{ route('frontend.profile.addresses') }}" class="text-sm text-primary-700 hover:text-primary-800">{{ __('Manage addresses') }}</a>
            </div>
            @if ($user->addresses->isEmpty())
                <p class="text-gray-500 dark:text-gray-400">{{ __('No addresses saved yet.') }}</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($user->addresses as $address)
                        <div class="p-4 border border-gray-200 dark:border-white/10 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $address->full_name ?? ($address->first_name.' '.$address->last_name) }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $address->full_address }}</p>
                            @if ($address->is_default)
                                <span class="inline-flex items-center mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-primary-100 text-primary-700">{{ __('Default') }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-layouts.base>
