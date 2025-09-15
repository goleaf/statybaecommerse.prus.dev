@extends('layouts.app')

@section('title', __('referrals.referral_details'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900">{{ __('referrals.referral_details') }}</h1>
                <a href="{{ route('referrals.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('common.back') }}
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('referrals.referral_code') }}</label>
                        <p class="mt-1 text-sm text-gray-900 font-mono bg-gray-100 p-2 rounded">{{ $referral->referral_code }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('referrals.status') }}</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($referral->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($referral->status === 'completed') bg-green-100 text-green-800
                            @elseif($referral->status === 'expired') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ __('referrals.statuses.' . $referral->status) }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('referrals.referred_user') }}</label>
                        <p class="mt-1 text-sm text-gray-900">
                            @if($referral->referred)
                                {{ $referral->referred->name }} ({{ $referral->referred->email }})
                            @else
                                {{ __('referrals.not_registered_yet') }}
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('referrals.created_at') }}</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $referral->created_at->format('d.m.Y H:i') }}</p>
                    </div>

                    @if($referral->expires_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('referrals.expires_at') }}</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $referral->expires_at->format('d.m.Y H:i') }}</p>
                    </div>
                    @endif
                </div>

                <div class="space-y-4">
                    @if($referral->rewards->count() > 0)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('referrals.rewards') }}</label>
                        <div class="mt-2 space-y-2">
                            @foreach($referral->rewards as $reward)
                            <div class="bg-gray-50 p-3 rounded">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium">{{ $reward->type }}</span>
                                    <span class="text-sm text-gray-600">{{ number_format($reward->amount, 2) }} {{ $reward->currency ?? 'EUR' }}</span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ __('referrals.reward_statuses.' . $reward->status) }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($referral->analyticsEvents->count() > 0)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('referrals.analytics_events') }}</label>
                        <div class="mt-2 space-y-2">
                            @foreach($referral->analyticsEvents as $event)
                            <div class="bg-gray-50 p-3 rounded">
                                <div class="text-sm font-medium">{{ $event->event_type }}</div>
                                <div class="text-xs text-gray-500">{{ $event->created_at->format('d.m.Y H:i') }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('referrals.share_referral') }}</h3>
                <div class="flex items-center space-x-4">
                    <input type="text" 
                           value="{{ route('referrals.track', $referral->referral_code) }}" 
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           readonly>
                    <button onclick="copyToClipboard(this.previousElementSibling)" 
                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        {{ __('common.copy') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(element) {
    element.select();
    element.setSelectionRange(0, 99999);
    document.execCommand('copy');
    
    // Show success message
    const button = element.nextElementSibling;
    const originalText = button.textContent;
    button.textContent = '{{ __("common.copied") }}';
    button.classList.remove('bg-green-500', 'hover:bg-green-700');
    button.classList.add('bg-green-600');
    
    setTimeout(() => {
        button.textContent = originalText;
        button.classList.remove('bg-green-600');
        button.classList.add('bg-green-500', 'hover:bg-green-700');
    }, 2000);
}
</script>
@endsection
