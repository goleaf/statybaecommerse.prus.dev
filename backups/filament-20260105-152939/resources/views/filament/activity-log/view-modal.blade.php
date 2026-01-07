<div class="space-y-6">
    <!-- Activity Details -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <x-heroicon-o-information-circle class="w-5 h-5 mr-2 text-blue-500" />
            {{ __('admin.activity_logs.details.title') }}
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.activity_logs.table.description') }}</dt>
                    <dd class="text-sm text-gray-900 dark:text-white mt-1">{{ $activity->description }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.activity_logs.table.log_name') }}</dt>
                    <dd class="text-sm text-gray-900 dark:text-white mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ __('admin.activity_logs.log_types.' . $activity->log_name, [], $activity->log_name) }}
                        </span>
                    </dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.activity_logs.table.event') }}</dt>
                    <dd class="text-sm text-gray-900 dark:text-white mt-1">
                        @if($activity->event)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @if($activity->event === 'created') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @elseif($activity->event === 'updated') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                @elseif($activity->event === 'deleted') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                @endif">
                                {{ __('admin.activity_logs.events.' . $activity->event, [], $activity->event) }}
                            </span>
                        @else
                            <span class="text-gray-500 dark:text-gray-400">-</span>
                        @endif
                    </dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.activity_logs.table.subject_type') }}</dt>
                    <dd class="text-sm text-gray-900 dark:text-white mt-1">
                        @if($activity->subject_type)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                {{ __('admin.activity_logs.subject_types.' . $activity->subject_type, [], class_basename($activity->subject_type)) }}
                                @if($activity->subject_id)
                                    #{{ $activity->subject_id }}
                                @endif
                            </span>
                        @else
                            <span class="text-gray-500 dark:text-gray-400">-</span>
                        @endif
                    </dd>
                </div>
            </div>
            
            <div class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.activity_logs.table.user') }}</dt>
                    <dd class="text-sm text-gray-900 dark:text-white mt-1">
                        @if($activity->causer)
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <div class="h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ substr($activity->causer->name, 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $activity->causer->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $activity->causer->email }}</p>
                                </div>
                            </div>
                        @else
                            <span class="text-gray-500 dark:text-gray-400">{{ __('admin.activity_logs.details.system_generated') }}</span>
                        @endif
                    </dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.activity_logs.table.date') }}</dt>
                    <dd class="text-sm text-gray-900 dark:text-white mt-1">
                        <div class="flex items-center">
                            <x-heroicon-o-clock class="w-4 h-4 mr-1 text-gray-400" />
                            {{ $activity->created_at->format('Y-m-d H:i:s') }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $activity->created_at->diffForHumans() }}
                        </div>
                    </dd>
                </div>
                
                @if($activity->ip_address)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.activity_logs.table.ip_address') }}</dt>
                    <dd class="text-sm text-gray-900 dark:text-white mt-1 font-mono">{{ $activity->ip_address }}</dd>
                </div>
                @endif
                
                @if($activity->user_agent)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.activity_logs.table.user_agent') }}</dt>
                    <dd class="text-sm text-gray-900 dark:text-white mt-1 font-mono text-xs break-all">{{ $activity->user_agent }}</dd>
                </div>
                @endif
                
                @if($activity->batch_uuid)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.activity_logs.table.batch_uuid') }}</dt>
                    <dd class="text-sm text-gray-900 dark:text-white mt-1 font-mono text-xs">{{ $activity->batch_uuid }}</dd>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Changes Section -->
    @if(!empty($properties))
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <x-heroicon-o-document-text class="w-5 h-5 mr-2 text-green-500" />
            {{ __('admin.activity_logs.details.changes') }}
        </h3>
        
        <div class="space-y-4">
            @if(isset($properties['attributes']) && !empty($properties['attributes']))
                <div>
                    <h4 class="text-sm font-medium text-green-600 dark:text-green-400 mb-2 flex items-center">
                        <x-heroicon-o-plus-circle class="w-4 h-4 mr-1" />
                        {{ __('admin.activity_logs.details.new_values') }}
                    </h4>
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <pre class="text-xs text-green-800 dark:text-green-200 whitespace-pre-wrap overflow-x-auto">{{ json_encode($properties['attributes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            @endif

            @if(isset($properties['old']) && !empty($properties['old']))
                <div>
                    <h4 class="text-sm font-medium text-red-600 dark:text-red-400 mb-2 flex items-center">
                        <x-heroicon-o-minus-circle class="w-4 h-4 mr-1" />
                        {{ __('admin.activity_logs.details.old_values') }}
                    </h4>
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <pre class="text-xs text-red-800 dark:text-red-200 whitespace-pre-wrap overflow-x-auto">{{ json_encode($properties['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            @endif
            
            @if(empty($properties['attributes']) && empty($properties['old']))
                <div class="text-center py-8">
                    <x-heroicon-o-document class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                    <p class="text-gray-500 dark:text-gray-400">{{ __('admin.activity_logs.details.no_changes') }}</p>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
