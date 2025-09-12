@if(config('app.env') !== 'testing')
    @vite('resources/js/live-notifications.js')
@endif
