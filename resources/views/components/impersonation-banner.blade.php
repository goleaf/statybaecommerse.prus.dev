@if(session()->has('impersonating') && isset($impersonating))
<div class="bg-orange-500 text-white px-4 py-2 text-center text-sm font-medium">
    <div class="flex items-center justify-center space-x-4">
        <span>
            {{ __('You are impersonating') }}: <strong>{{ $impersonating['user']->name }}</strong>
        </span>
        <a href="{{ route('admin.stop-impersonating') }}" 
           class="bg-orange-600 hover:bg-orange-700 px-3 py-1 rounded text-xs font-semibold transition-colors">
            {{ __('Stop Impersonating') }}
        </a>
    </div>
</div>
@endif
