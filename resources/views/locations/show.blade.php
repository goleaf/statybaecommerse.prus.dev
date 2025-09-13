@extends('layouts.app')

@section('title', $location->display_name)
@section('description', $location->translated_description)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <li><a href="{{ route('locations.index') }}" class="hover:text-blue-600">{{ __('locations.page_title') }}</a></li>
            <li><i class="fas fa-chevron-right"></i></li>
            <li class="text-gray-900">{{ $location->display_name }}</li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h1 class="text-3xl font-bold text-gray-900">{{ $location->display_name }}</h1>
                        <span class="px-3 py-1 text-sm font-medium rounded-full {{ $location->type === 'warehouse' ? 'bg-blue-100 text-blue-800' : ($location->type === 'store' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ $location->type_label }}
                        </span>
                    </div>

                    @if($location->translated_description)
                        <div class="mb-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-3">{{ __('locations.additional_info') }}</h2>
                            <p class="text-gray-700 leading-relaxed">{{ $location->translated_description }}</p>
                        </div>
                    @endif

                    <!-- Address Information -->
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">{{ __('locations.full_address') }}</h2>
                        <div class="bg-gray-50 rounded-lg p-4">
                            @if($location->full_address)
                                <p class="text-gray-700 mb-2">
                                    <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>
                                    {{ $location->full_address }}
                                </p>
                            @endif

                            @if($location->country)
                                <p class="text-gray-600">
                                    <i class="fas fa-flag mr-2"></i>
                                    {{ $location->country->translated_name }}
                                </p>
                            @endif

                            @if($location->hasCoordinates())
                                <div class="mt-3">
                                    <a href="{{ $location->google_maps_url }}" 
                                       target="_blank"
                                       class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-external-link-alt mr-2"></i>
                                        {{ __('locations.get_directions') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">{{ __('locations.contact_information') }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($location->phone)
                                <div class="flex items-center">
                                    <i class="fas fa-phone mr-3 text-blue-600"></i>
                                    <div>
                                        <p class="text-sm text-gray-500">{{ __('locations.phone') }}</p>
                                        <a href="tel:{{ $location->phone }}" class="text-gray-900 hover:text-blue-600">{{ $location->phone }}</a>
                                    </div>
                                </div>
                            @endif

                            @if($location->email)
                                <div class="flex items-center">
                                    <i class="fas fa-envelope mr-3 text-blue-600"></i>
                                    <div>
                                        <p class="text-sm text-gray-500">{{ __('locations.email') }}</p>
                                        <a href="mailto:{{ $location->email }}" class="text-gray-900 hover:text-blue-600">{{ $location->email }}</a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Opening Hours -->
                    @if($location->hasOpeningHours())
                        <div class="mb-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-3">{{ __('locations.working_hours') }}</h2>
                            <div class="bg-gray-50 rounded-lg p-4">
                                @foreach($location->getFormattedOpeningHours() as $day => $hours)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
                                        <span class="font-medium text-gray-900">{{ $hours['day'] }}</span>
                                        <span class="text-gray-600">
                                            @if($hours['is_closed'])
                                                {{ __('locations.closed_all_day') }}
                                            @elseif($hours['open_time'] && $hours['close_time'])
                                                {{ $hours['open_time'] }} - {{ $hours['close_time'] }}
                                            @else
                                                {{ __('locations.closed') }}
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Contact Form -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">{{ __('locations.contact_form_title') }}</h3>
                
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('locations.contact', $location) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('locations.your_name') }}
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('locations.your_email') }}
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('locations.subject') }}
                            </label>
                            <input type="text" 
                                   id="subject" 
                                   name="subject" 
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('locations.message') }}
                            </label>
                            <textarea id="message" 
                                      name="message" 
                                      rows="4" 
                                      required
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <button type="submit" 
                                class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            {{ __('locations.send_message') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Related Locations -->
            @if($relatedLocations->count() > 0)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">{{ __('locations.related_locations') }}</h3>
                    <div class="space-y-4">
                        @foreach($relatedLocations as $relatedLocation)
                            <div class="border-b border-gray-200 pb-4 last:border-b-0 last:pb-0">
                                <h4 class="font-medium text-gray-900 mb-1">{{ $relatedLocation->display_name }}</h4>
                                <p class="text-sm text-gray-600 mb-2">{{ $relatedLocation->full_address }}</p>
                                <a href="{{ route('locations.show', $relatedLocation) }}" 
                                   class="text-blue-600 hover:text-blue-800 text-sm">
                                    {{ __('locations.view_details') }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection