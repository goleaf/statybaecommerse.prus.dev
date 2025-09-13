@extends('layouts.app')

@section('title', $reward->localized_title)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-4 mb-4">
                <a href="{{ route('frontend.referral-rewards.index') }}" 
                   class="inline-flex items-center text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    {{ __('referrals.frontend.back_to_rewards') }}
                </a>
            </div>
            
            <div class="flex items-center space-x-4">
                @if($reward->type === 'referrer_bonus')
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                @else
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                @endif
                
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $reward->localized_title }}
                    </h1>
                    <div class="flex items-center space-x-4 mt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($reward->type === 'referrer_bonus') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                            {{ $reward->type === 'referrer_bonus' ? __('referrals.types.referrer_bonus') : __('referrals.types.referred_discount') }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($reward->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                            @elseif($reward->status === 'applied') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                            {{ __('referrals.status.' . $reward->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Description -->
                @if($reward->localized_description)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            {{ __('referrals.forms.description') }}
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400">
                            {{ $reward->localized_description }}
                        </p>
                    </div>
                @endif

                <!-- Reward Details -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        {{ __('referrals.forms.reward_details') }}
                    </h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('referrals.forms.amount') }}
                            </dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                €{{ number_format($reward->amount, 2) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('referrals.forms.currency') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $reward->currency_code }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('referrals.forms.created_at') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $reward->created_at->format('M d, Y H:i') }}
                            </dd>
                        </div>
                        @if($reward->applied_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('referrals.forms.applied_at') }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $reward->applied_at->format('M d, Y H:i') }}
                                </dd>
                            </div>
                        @endif
                        @if($reward->expires_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('referrals.forms.expires_at') }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $reward->expires_at->format('M d, Y H:i') }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <!-- Related Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        {{ __('referrals.frontend.related_information') }}
                    </h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('referrals.forms.referral_code') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $reward->referral->referral_code }}
                            </dd>
                        </div>
                        @if($reward->order)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('referrals.forms.order') }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    #{{ $reward->order->id }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Status Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        {{ __('referrals.forms.status') }}
                    </h3>
                    <div class="flex items-center space-x-3">
                        @if($reward->status === 'pending')
                            <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                            <span class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                {{ __('referrals.status.pending') }}
                            </span>
                        @elseif($reward->status === 'applied')
                            <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                            <span class="text-sm font-medium text-green-800 dark:text-green-200">
                                {{ __('referrals.status.applied') }}
                            </span>
                        @else
                            <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                            <span class="text-sm font-medium text-red-800 dark:text-red-200">
                                {{ __('referrals.status.expired') }}
                            </span>
                        @endif
                    </div>
                    @if($reward->isValid())
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            {{ __('referrals.frontend.reward_valid') }}
                        </p>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            {{ __('referrals.frontend.reward_invalid') }}
                        </p>
                    @endif
                </div>

                <!-- Actions -->
                @if($reward->status === 'pending' && $reward->isValid())
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            {{ __('referrals.frontend.actions') }}
                        </h3>
                        <div class="space-y-3">
                            <button type="button" 
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                {{ __('referrals.actions.apply_reward') }}
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
