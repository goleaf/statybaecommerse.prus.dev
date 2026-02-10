@php
    $resources = [
        \App\Filament\Resources\ProductResource::class,
        \App\Filament\Resources\CategoryResource::class,
        \App\Filament\Resources\ProductVariantResource::class,
        \App\Filament\Resources\InventoryResource::class,
        \App\Filament\Resources\BrandResource::class,
        \App\Filament\Resources\ProductImageResource::class,
        \App\Filament\Resources\ProductFeatureResource::class,
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
