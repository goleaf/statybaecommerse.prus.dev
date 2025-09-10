<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.templates.account')] class extends Component {
    public array $notifications = [];

    public function mount(): void
    {
        $user = auth()->user();
        if ($user && method_exists($user, 'notifications') && \Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            $this->notifications = $user
                ->notifications()
                ->latest()
                ->limit(100)
                ->get(['id', 'type', 'data', 'read_at', 'created_at'])
                ->map(function ($n) {
                    return [
                        'id' => $n->id,
                        'type' => class_basename($n->type),
                        'data' => $n->data,
                        'read_at' => optional($n->read_at)->toDateTimeString(),
                        'created_at' => optional($n->created_at)->toDateTimeString(),
                    ];
                })
                ->toArray();
        }
    }
}; ?>

<div class="space-y-10">
    <x-breadcrumbs :items="[['label' => __('My account'), 'url' => route('account')], ['label' => __('Notifications')]]" />
    <x-page-heading :title="__('Notifications')" :description="__('Messages and alerts')" />

    @if (empty($notifications))
        <p class="text-sm text-gray-500">{{ __('No notifications yet.') }}</p>
    @else
        <div class="divide-y divide-gray-200 rounded-md border border-gray-200 bg-white">
            @foreach ($notifications as $n)
                <div class="p-4 space-y-1">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-medium text-gray-900">{{ $n['type'] }}</div>
                        <div class="text-xs text-gray-500">{{ $n['created_at'] }}</div>
                    </div>
                    <div class="text-sm text-gray-700">{{ json_encode($n['data']) }}</div>
                    @if (!empty($n['read_at']))
                        <div class="text-xs text-gray-500">{{ __('Read at') }}: {{ $n['read_at'] }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
