@php($layout = 'layouts.templates.app')

@section('meta')
    <x-meta :title="$location->name . ' - ' . __('Locations') . ' - ' . config('app.name')"
            :description="trim($location->street_address . ', ' . $location->city . ' ' . $location->postal_code)"
            canonical="{{ url()->current() }}" />
@endsection

<x-dynamic-component :component="$layout">
    <div class="container mx-auto px-4 py-8" wire:loading.attr="aria-busy" aria-busy="false">
        @if (session('status'))
            <x-alert type="success" class="mb-4">{{ session('status') }}</x-alert>
        @endif
        @if (session('error'))
            <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
        @endif
        @if ($errors->any())
            <x-alert type="error" class="mb-4">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <x-breadcrumbs :items="[
            ['label' => __('Locations'), 'url' => route('locations.index', ['locale' => app()->getLocale()])],
            ['label' => $location->name],
        ]" />

        <h1 class="text-2xl font-semibold mb-4">{{ $location->name }}</h1>

        <div class="space-y-2 text-sm text-slate-700">
            <div>{{ $location->street_address }}</div>
            <div>{{ $location->city }} {{ $location->postal_code }}</div>
            @if (!empty($location->country_name))
                <div>{{ $location->country_name }}</div>
            @endif
        </div>
    </div>
</x-dynamic-component>

@php($layout = 'layouts.templates.app')

<x-dynamic-component :component="$layout">
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold">{{ $location->name }}</h1>
            @if ($location->is_default)
                <span class="text-xs text-green-700 bg-green-100 px-2 py-0.5 rounded">{{ __('Default') }}</span>
            @endif
        </div>

        <div class="space-y-2 text-slate-700">
            <div><span class="font-semibold">{{ __('Email') }}:</span> {{ $location->email }}</div>
            <div><span class="font-semibold">{{ __('Phone') }}:</span> {{ $location->phone_number ?: '—' }}</div>
            <div><span class="font-semibold">{{ __('Address') }}:</span> {{ $location->street_address }},
                {{ $location->city }} {{ $location->postal_code }}</div>
        </div>

        <div class="mt-8">
            <h2 class="text-xl font-semibold mb-2">{{ __('About this Location') }}</h2>
            <div class="text-slate-600">{{ $location->description ?: __('No description provided.') }}</div>
        </div>
    </div>
</x-dynamic-component>
