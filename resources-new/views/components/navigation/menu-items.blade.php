@props(['items' => collect()])

@if ($items->isNotEmpty())
    <ul class="flex items-center gap-6">
        @foreach ($items as $item)
            @php
                $href = '#';
                if (!empty($item->route_name) && Route::has($item->route_name)) {
                    $href = route(
                        $item->route_name,
                        array_merge($item->route_params ?? [], ['locale' => app()->getLocale()]),
                    );
                } elseif (!empty($item->url)) {
                    $href = $item->url;
                }
            @endphp
            <li class="relative group">
                <a href="{{ $href }}"
                   class="text-sm font-medium text-gray-700 hover:text-gray-900 flex items-center gap-2">
                    @if ($item->icon)
                        <x-app-icon :name="$item->icon" class="w-4 h-4" />
                    @endif
                    <span>{{ $item->label }}</span>
                </a>

                @if ($item->children->where('is_visible', true)->count())
                    <div class="absolute left-0 mt-2 hidden group-hover:block z-30">
                        <div class="rounded-md border border-gray-200 bg-white shadow-lg p-3 min-w-[220px]">
                            <ul class="space-y-2">
                                @foreach ($item->children->where('is_visible', true) as $child)
                                    @php
                                        $childHref = '#';
                                        if (!empty($child->route_name) && Route::has($child->route_name)) {
                                            $childHref = route(
                                                $child->route_name,
                                                array_merge($child->route_params ?? [], [
                                                    'locale' => app()->getLocale(),
                                                ]),
                                            );
                                        } elseif (!empty($child->url)) {
                                            $childHref = $child->url;
                                        }
                                    @endphp
                                    <li>
                                        <a href="{{ $childHref }}"
                                           class="block text-sm text-gray-700 hover:text-gray-900 hover:underline">
                                            {{ $child->label }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
@endif
