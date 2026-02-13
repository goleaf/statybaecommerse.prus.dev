@php
    $resources = [
        \App\Filament\Resources\UserResource::class,
        \App\Filament\Resources\Companies\CompanyResource::class,
        \App\Filament\Resources\Partners\PartnerResource::class,
        \App\Filament\Resources\CustomerGroups\CustomerGroupResource::class,
        \App\Filament\Resources\Subscribers\SubscriberResource::class,
    ];

    $currentResource = null;
    foreach ($resources as $resource) {
        if (request()->routeIs($resource::getRouteBaseName() . '.*')) {
            $currentResource = $resource;
            break;
        }
    }
@endphp

@if ($currentResource)
    <div class="mb-6">
        <x-filament::tabs>
            @foreach ($resources as $resource)
                <x-filament::tabs.item 
                    :href="$resource::getUrl('index')"
                    :active="request()->routeIs($resource::getRouteBaseName() . '.*')"
                    tag="a"
                >
                    {{ $resource::getNavigationLabel() }}
                </x-filament::tabs.item>
            @endforeach
        </x-filament::tabs>
    </div>
@endif
