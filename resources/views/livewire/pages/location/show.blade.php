<div>
    <div class="container mx-auto px-4 py-8">
        <x-breadcrumbs :items="[
            ['label' => __('translations.locations'), 'url' => route('locations.index', ['locale' => app()->getLocale()])],
            ['label' => $location->name],
        ]" />

        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-8">
                <div class="flex items-start justify-between mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">{{ $location->name }}</h1>
                    @if($location->is_default)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            {{ __('translations.default_location') }}
                        </span>
                    @endif
                </div>

                <div class="grid gap-8 md:grid-cols-2">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('translations.address') }}</h2>
                        <div class="space-y-2 text-gray-600">
                            @if($location->address_line_1)
                                <div>{{ $location->address_line_1 }}</div>
                            @endif
                            @if($location->address_line_2)
                                <div>{{ $location->address_line_2 }}</div>
                            @endif
                            <div>
                                {{ $location->city }}
                                @if($location->state), {{ $location->state }}@endif
                                @if($location->postal_code) {{ $location->postal_code }}@endif
                            </div>
                            @if($location->country)
                                <div class="font-medium">{{ $location->country->trans('name') ?? $location->country->name }}</div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('translations.contact_information') }}</h2>
                        <div class="space-y-3">
                            @if($location->phone)
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <a href="tel:{{ $location->phone }}" class="text-blue-600 hover:text-blue-800">{{ $location->phone }}</a>
                                </div>
                            @endif
                            @if($location->email)
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <a href="mailto:{{ $location->email }}" class="text-blue-600 hover:text-blue-800">{{ $location->email }}</a>
                                </div>
                            @endif
                        </div>

                        @if($location->type)
                            <div class="mt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('translations.location_type') }}</h3>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ ucfirst($location->type) }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-8">
                    <a href="{{ route('locations.index', ['locale' => app()->getLocale()]) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        {{ __('translations.back_to_locations') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
