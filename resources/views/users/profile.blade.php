<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.templates.frontend');
title(__('users.profile'));

?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ localized_route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        {{ __('nav.home') }}
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('users.dashboard') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">{{ __('users.dashboard') }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('users.profile') }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('users.profile') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('users.profile_description') }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <!-- Avatar Section -->
                    <div class="text-center">
                        <div class="relative inline-block">
                            <img 
                                src="{{ $user->avatar_url ? Storage::disk('public')->url($user->avatar_url) : $user->generateGravatarUrl() }}" 
                                alt="{{ $user->name }}"
                                class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-white shadow-lg"
                                id="avatar-preview"
                            >
                            <label for="avatar-upload" class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full cursor-pointer hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </label>
                            <input type="file" id="avatar-upload" class="hidden" accept="image/*">
                        </div>
                        <h2 class="mt-4 text-xl font-semibold text-gray-900">{{ $user->name }}</h2>
                        <p class="text-gray-600">{{ $user->email }}</p>
                        
                        <!-- User Stats -->
                        <div class="mt-6 grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $user->orders_count }}</div>
                                <div class="text-sm text-gray-600">{{ __('users.orders') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">€{{ number_format($user->total_spent, 2) }}</div>
                                <div class="text-sm text-gray-600">{{ __('users.total_spent') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-sm font-medium text-gray-900 mb-3">{{ __('users.account_info') }}</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">{{ __('users.member_since') }}</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $user->created_at->format('M Y') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">{{ __('users.last_login') }}</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : __('users.never') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">{{ __('users.language') }}</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $user->locale_text }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
                    <nav class="p-4">
                        <ul class="space-y-2">
                            <li>
                                <a href="{{ route('users.profile') }}" class="flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-md">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ __('users.profile') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('users.dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6a2 2 0 01-2 2H10a2 2 0 01-2-2V5z"></path>
                                    </svg>
                                    {{ __('users.dashboard') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('users.orders') }}" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                    {{ __('users.orders') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('users.addresses') }}" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    {{ __('users.addresses') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('users.reviews') }}" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                    </svg>
                                    {{ __('users.reviews') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('users.wishlist') }}" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                    {{ __('users.wishlist') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('users.documents') }}" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    {{ __('users.documents') }}
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Profile Information Form -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('users.personal_information') }}</h3>
                    
                    <form action="{{ route('users.profile.update') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.name') }}</label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    value="{{ old('name', $user->name) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    required
                                >
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.email') }}</label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    value="{{ old('email', $user->email) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    required
                                >
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- First Name -->
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.first_name') }}</label>
                                <input 
                                    type="text" 
                                    id="first_name" 
                                    name="first_name" 
                                    value="{{ old('first_name', $user->first_name) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                >
                                @error('first_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.last_name') }}</label>
                                <input 
                                    type="text" 
                                    id="last_name" 
                                    name="last_name" 
                                    value="{{ old('last_name', $user->last_name) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                >
                                @error('last_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Phone Number -->
                            <div>
                                <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.phone_number') }}</label>
                                <input 
                                    type="tel" 
                                    id="phone_number" 
                                    name="phone_number" 
                                    value="{{ old('phone_number', $user->phone_number) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                >
                                @error('phone_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Gender -->
                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.gender') }}</label>
                                <select 
                                    id="gender" 
                                    name="gender"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">{{ __('users.select_gender') }}</option>
                                    <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>{{ __('users.male') }}</option>
                                    <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>{{ __('users.female') }}</option>
                                    <option value="other" {{ old('gender', $user->gender) == 'other' ? 'selected' : '' }}>{{ __('users.other') }}</option>
                                </select>
                                @error('gender')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Birth Date -->
                            <div>
                                <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.birth_date') }}</label>
                                <input 
                                    type="date" 
                                    id="birth_date" 
                                    name="birth_date" 
                                    value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                >
                                @error('birth_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Preferred Locale -->
                            <div>
                                <label for="preferred_locale" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.preferred_language') }}</label>
                                <select 
                                    id="preferred_locale" 
                                    name="preferred_locale"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    required
                                >
                                    <option value="lt" {{ old('preferred_locale', $user->preferred_locale) == 'lt' ? 'selected' : '' }}>{{ __('users.lithuanian') }}</option>
                                    <option value="en" {{ old('preferred_locale', $user->preferred_locale) == 'en' ? 'selected' : '' }}>{{ __('users.english') }}</option>
                                </select>
                                @error('preferred_locale')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Bio -->
                        <div>
                            <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.bio') }}</label>
                            <textarea 
                                id="bio" 
                                name="bio" 
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="{{ __('users.bio_placeholder') }}"
                            >{{ old('bio', $user->bio) }}</textarea>
                            @error('bio')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Company and Position -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="company" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.company') }}</label>
                                <input 
                                    type="text" 
                                    id="company" 
                                    name="company" 
                                    value="{{ old('company', $user->company) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                >
                                @error('company')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="position" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.position') }}</label>
                                <input 
                                    type="text" 
                                    id="position" 
                                    name="position" 
                                    value="{{ old('position', $user->position) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                >
                                @error('position')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Website -->
                        <div>
                            <label for="website" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.website') }}</label>
                            <input 
                                type="url" 
                                id="website" 
                                name="website" 
                                value="{{ old('website', $user->website) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="https://example.com"
                            >
                            @error('website')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button 
                                type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                {{ __('users.update_profile') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Form -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('users.change_password') }}</h3>
                    
                    <form action="{{ route('users.password.update') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Current Password -->
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.current_password') }}</label>
                                <input 
                                    type="password" 
                                    id="current_password" 
                                    name="current_password" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    required
                                >
                                @error('current_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- New Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.new_password') }}</label>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    required
                                >
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.confirm_password') }}</label>
                            <input 
                                type="password" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                required
                            >
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button 
                                type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                {{ __('users.update_password') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Danger Zone -->
                <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6">
                    <h3 class="text-lg font-medium text-red-900 mb-4">{{ __('users.danger_zone') }}</h3>
                    <p class="text-sm text-red-600 mb-4">{{ __('users.danger_zone_description') }}</p>
                    
                    <div class="flex space-x-4">
                        <!-- Deactivate Account -->
                        <button 
                            type="button"
                            onclick="confirmDeactivate()"
                            class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        >
                            {{ __('users.deactivate_account') }}
                        </button>

                        <!-- Delete Account -->
                        <button 
                            type="button"
                            onclick="confirmDelete()"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        >
                            {{ __('users.delete_account') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Avatar Upload Script -->
<script>
document.getElementById('avatar-upload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const formData = new FormData();
        formData.append('avatar', file);
        
        fetch('{{ route("users.avatar.update") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('avatar-preview').src = data.avatar_url;
                // Show success message
                alert(data.message);
            } else {
                alert('Error updating avatar');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating avatar');
        });
    }
});
</script>

<!-- Danger Zone Scripts -->
<script>
function confirmDeactivate() {
    if (confirm('{{ __("users.confirm_deactivate") }}')) {
        // Show password confirmation modal
        const password = prompt('{{ __("users.enter_password_to_deactivate") }}');
        if (password) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("users.deactivate") }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const passwordInput = document.createElement('input');
            passwordInput.type = 'hidden';
            passwordInput.name = 'password';
            passwordInput.value = password;
            
            form.appendChild(csrfToken);
            form.appendChild(passwordInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
}

function confirmDelete() {
    if (confirm('{{ __("users.confirm_delete") }}')) {
        const password = prompt('{{ __("users.enter_password_to_delete") }}');
        if (password) {
            const confirmation = prompt('{{ __("users.type_delete_to_confirm") }}');
            if (confirmation === 'DELETE') {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("users.delete") }}';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                const passwordInput = document.createElement('input');
                passwordInput.type = 'hidden';
                passwordInput.name = 'password';
                passwordInput.value = password;
                
                const confirmationInput = document.createElement('input');
                confirmationInput.type = 'hidden';
                confirmationInput.name = 'confirmation';
                confirmationInput.value = '1';
                
                form.appendChild(csrfToken);
                form.appendChild(passwordInput);
                form.appendChild(confirmationInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    }
}
</script>
