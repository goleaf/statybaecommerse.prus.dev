<div>
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('translations.locations') }}</h1>
        </div>

        @if($locations->isEmpty())
            <div class="text-center py-12">
                <div class="text-gray-500 text-lg">{{ __('translations.no_locations_found') }}</div>
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($locations as $location)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <h3 class="text-xl font-semibold text-gray-900">{{ $location->name }}</h3>
                                @if($location->is_default)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('translations.default') }}
                                    </span>
                                @endif
                            </div>

                            <div class="space-y-2 text-sm text-gray-600 mb-4">
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
                                    <div>{{ $location->country->trans('name') ?? $location->country->name }}</div>
                                @endif
                            </div>

                            @if($location->phone || $location->email)
                                <div class="border-t pt-4 space-y-2">
                                    @if($location->phone)
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            <a href="tel:{{ $location->phone }}" class="hover:text-blue-600">{{ $location->phone }}</a>
                                        </div>
                                    @endif
                                    @if($location->email)
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            <a href="mailto:{{ $location->email }}" class="hover:text-blue-600">{{ $location->email }}</a>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($location->type)
                                <div class="mt-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst($location->type) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
