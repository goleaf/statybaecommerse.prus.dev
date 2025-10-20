@php
    $causerName = $activity->causer?->name ?? __('System');
    $subject = $activity->subject;
    $subjectType = (string) $activity->subject_type;
    $subjectTypeName = filled($subjectType)
        ? class_basename($subjectType)
        : __('N/A');

    $subjectName = $subject && method_exists($subject, 'getAttribute')
        ? ($subject->getAttribute('name') ?? $subjectTypeName)
        : $subjectTypeName;
    $properties = collect($activity->properties?->toArray() ?? []);
@endphp

<div class="space-y-4">
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="space-y-1">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Description') }}</p>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $activity->description }}</p>
        </div>
        <div class="space-y-1">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Causer') }}</p>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $causerName }}</p>
        </div>
        <div class="space-y-1">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Log Name') }}</p>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $activity->log_name ?? __('N/A') }}</p>
        </div>
        <div class="space-y-1">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Subject') }}</p>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $subjectName }}</p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="space-y-1">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Event') }}</p>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $activity->event ?? __('N/A') }}</p>
        </div>
        <div class="space-y-1">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Created At') }}</p>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ optional($activity->created_at)->toDayDateTimeString() }}</p>
        </div>
    </div>

    @if($properties->isNotEmpty())
        <div class="space-y-2">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Properties') }}</p>
            <pre class="overflow-x-auto rounded-lg bg-gray-900/80 p-3 text-xs text-gray-100 dark:bg-gray-800/80">
{{ json_encode($properties->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}
            </pre>
        </div>
    @endif
</div>
