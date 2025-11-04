@if(auth()->check())
    <div class="flex items-center space-x-4">
        @livewire('live-notification-feed')
    </div>
@endif
