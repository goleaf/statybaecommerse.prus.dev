@php($layout = 'layouts.templates.app')

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
        <h1 class="text-2xl font-semibold mb-6">{{ __('Locations') }}</h1>
        @section('meta')
            <x-meta :title="__('Locations') . ' - ' . config('app.name')"
                    :description="__('Store locations and pickup points')"
                    canonical="{{ url()->current() }}" />
        @endsection

        @if ($locations->isEmpty())
            <div class="text-slate-500">{{ __('No locations defined yet.') }}</div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($locations as $location)
                    <a href="{{ route('locations.show', ['locale' => app()->getLocale(), 'id' => $location->id]) }}"
                       class="block border rounded-lg p-4 hover:shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="text-lg font-medium">{{ $location->name }}</div>
                            @if ($location->is_default)
                                <span
                                      class="text-xs text-green-700 bg-green-100 px-2 py-0.5 rounded">{{ __('Default') }}</span>
                            @endif
                        </div>
                        <div class="text-sm text-slate-500">
                            {{ $location->street_address }}, {{ $location->city }} {{ $location->postal_code }}
                        </div>
                    </a>
                @endforeach
            </div>

            <nav class="mt-6" aria-label="Pagination">{{ $locations->links() }}</nav>
        @endif
    </div>
</x-dynamic-component>
