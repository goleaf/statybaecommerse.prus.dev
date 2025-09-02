<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Activity Details') }}</h3>
            <dl class="mt-2 space-y-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Description') }}</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $activity->description }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Log Name') }}</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $activity->log_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Subject') }}</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">
                        {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('User') }}</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">
                        {{ $activity->causer?->name ?? __('System') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Date') }}</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">
                        {{ $activity->created_at->format('Y-m-d H:i:s') }}
                    </dd>
                </div>
            </dl>
        </div>

        @if(!empty($properties))
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Changes') }}</h3>
            <div class="mt-2 space-y-2">
                @if(isset($properties['attributes']))
                    <div>
                        <h4 class="text-sm font-medium text-green-600 dark:text-green-400">{{ __('New Values') }}</h4>
                        <div class="mt-1 p-2 bg-green-50 dark:bg-green-900/20 rounded text-xs">
                            <pre class="whitespace-pre-wrap">{{ json_encode($properties['attributes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                @endif

                @if(isset($properties['old']))
                    <div>
                        <h4 class="text-sm font-medium text-red-600 dark:text-red-400">{{ __('Old Values') }}</h4>
                        <div class="mt-1 p-2 bg-red-50 dark:bg-red-900/20 rounded text-xs">
                            <pre class="whitespace-pre-wrap">{{ json_encode($properties['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
