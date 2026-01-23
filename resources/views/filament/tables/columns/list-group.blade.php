@props([
    'items' => [],
])

@if (filled($items))
    <ul class="space-y-1 text-sm">
        @foreach ($items as $item)
            @php
                $color = $item['color'] ?? 'primary';
                $colorClasses = match ($color) {
                    'danger' => 'text-danger-600 hover:text-danger-700 dark:text-danger-400 dark:hover:text-danger-300',
                    'success' => 'text-success-600 hover:text-success-700 dark:text-success-400 dark:hover:text-success-300',
                    'warning' => 'text-warning-600 hover:text-warning-700 dark:text-warning-400 dark:hover:text-warning-300',
                    'info' => 'text-info-600 hover:text-info-700 dark:text-info-400 dark:hover:text-info-300',
                    'secondary' => 'text-gray-600 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-200',
                    default => 'text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300',
                };
            @endphp
            <li>
                <a
                    href="{{ $item['url'] ?? '#' }}"
                    @class([
                        'inline-flex items-center gap-2 transition-colors',
                        $colorClasses,
                    ])
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    @if (! empty($item['icon']))
                        <x-filament::icon
                            :icon="$item['icon']"
                            class="h-4 w-4"
                        />
                    @endif

                    <span class="truncate">
                        {{ $item['label'] ?? '' }}
                    </span>
                </a>
            </li>
        @endforeach
    </ul>
@else
    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.list_group.no_quick_links') }}</span>
@endif
