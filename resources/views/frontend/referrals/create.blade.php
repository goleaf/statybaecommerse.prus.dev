@extends('frontend.layouts.app')

@section('title', __('referrals.refer_friends'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ __('referrals.refer_friends') }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ __('referrals.refer_friends_description') }}
            </p>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ __('referrals.create_referral') }}
                </h3>
            </div>

            <form action="{{ route('referrals.store') }}" method="POST" class="px-6 py-6">
                @csrf

                <!-- Referred User Email -->
                <div class="mb-6">
                    <label for="referred_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('referrals.referred_user_email') }}
                    </label>
                    <input type="email" 
                           id="referred_email" 
                           name="referred_email" 
                           value="{{ old('referred_email') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('referred_email') border-red-500 @enderror"
                           placeholder="{{ __('referrals.enter_email') }}"
                           required>
                    @error('referred_email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('referrals.referred_email_help') }}
                    </p>
                </div>

                <!-- Title -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('referrals.title') }}
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('title') border-red-500 @enderror"
                           placeholder="{{ __('referrals.enter_title') }}">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('referrals.title_help') }}
                    </p>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('referrals.description') }}
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('description') border-red-500 @enderror"
                              placeholder="{{ __('referrals.enter_description') }}">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('referrals.description_help') }}
                    </p>
                </div>

                <!-- Benefits Section -->
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">
                        {{ __('referrals.referral_benefits') }}
                    </h4>
                    <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                        <li>• {{ __('referrals.benefit_1') }}</li>
                        <li>• {{ __('referrals.benefit_2') }}</li>
                        <li>• {{ __('referrals.benefit_3') }}</li>
                    </ul>
                </div>

                <!-- How It Works Section -->
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <h4 class="text-sm font-medium text-green-900 dark:text-green-100 mb-2">
                        {{ __('referrals.how_it_works') }}
                    </h4>
                    <ol class="text-sm text-green-800 dark:text-green-200 space-y-1">
                        <li>1. {{ __('referrals.step_1') }}</li>
                        <li>2. {{ __('referrals.step_2') }}</li>
                        <li>3. {{ __('referrals.step_3') }}</li>
                        <li>4. {{ __('referrals.step_4') }}</li>
                    </ol>
                </div>

                <!-- Terms and Conditions -->
                <div class="mb-6">
                    <div class="flex items-start">
                        <input type="checkbox" 
                               id="terms" 
                               name="terms" 
                               class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                               required>
                        <label for="terms" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            {{ __('referrals.agree_terms') }}
                            <a href="{{ route('terms') }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                {{ __('referrals.terms_conditions') }}
                            </a>
                        </label>
                    </div>
                    @error('terms')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between">
                    <a href="{{ route('referrals.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        {{ __('common.back') }}
                    </a>

                    <button type="submit" 
                            class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        {{ __('referrals.create_referral') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Additional Information -->
        <div class="mt-8 bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                {{ __('referrals.additional_information') }}
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                        {{ __('referrals.referral_limits') }}
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('referrals.referral_limits_description') }}
                    </p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                        {{ __('referrals.reward_system') }}
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('referrals.reward_system_description') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



