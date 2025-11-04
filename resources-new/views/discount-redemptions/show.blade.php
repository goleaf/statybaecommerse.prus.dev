@extends('components.layouts.base')

@section('title', __('frontend.discount_redemptions.show.title', ['id' => $discountRedemption->id]))
@section('description', __('frontend.discount_redemptions.show.description'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ __('frontend.discount_redemptions.show.title', ['id' => $discountRedemption->id]) }}
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        {{ __('frontend.discount_redemptions.show.description') }}
                    </p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('frontend.discount-redemptions.index') }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                        {{ __('frontend.discount_redemptions.actions.back_to_list') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Status Badge -->
        <div class="mb-6">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                @if($discountRedemption->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                @elseif($discountRedemption->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                @elseif($discountRedemption->status === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                @endif">
                {{ __('frontend.discount_redemptions.status.' . $discountRedemption->status) }}
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Information -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ __('frontend.discount_redemptions.show.main_information') }}
                        </h2>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('frontend.discount_redemptions.fields.discount') }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $discountRedemption->discount->name ?? __('frontend.discount_redemptions.unknown_discount') }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('frontend.discount_redemptions.fields.discount_code') }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    <span class="font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                        {{ $discountRedemption->code->code ?? __('frontend.discount_redemptions.unknown_code') }}
                                    </span>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('frontend.discount_redemptions.fields.amount_saved') }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    <span class="text-lg font-semibold text-green-600 dark:text-green-400">
                                        €{{ number_format($discountRedemption->amount_saved, 2) }}
                                    </span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">
                                        {{ $discountRedemption->currency_code }}
                                    </span>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('frontend.discount_redemptions.fields.redeemed_at') }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $discountRedemption->redeemed_at?->format('d.m.Y H:i:s') }}
                                </dd>
                            </div>

                            @if($discountRedemption->order)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('frontend.discount_redemptions.fields.order') }}
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                        <a href="{{ route('frontend.orders.show', $discountRedemption->order) }}" 
                                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                            {{ $discountRedemption->order->order_number }}
                                        </a>
                                    </dd>
                                </div>
                            @endif

                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('frontend.discount_redemptions.fields.created_at') }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $discountRedemption->created_at->format('d.m.Y H:i:s') }}
                                </dd>
                            </div>
                        </dl>

                        @if($discountRedemption->notes)
                            <div class="mt-6">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('frontend.discount_redemptions.fields.notes') }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-3">
                                        {{ $discountRedemption->notes }}
                                    </div>
                                </dd>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Discount Details -->
                @if($discountRedemption->discount)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mt-6">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ __('frontend.discount_redemptions.show.discount_details') }}
                            </h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('frontend.discounts.fields.type') }}
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($discountRedemption->discount->type === 'percentage') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @endif">
                                            {{ __('frontend.discounts.types.' . $discountRedemption->discount->type) }}
                                        </span>
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('frontend.discounts.fields.value') }}
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                        @if($discountRedemption->discount->type === 'percentage')
                                            {{ $discountRedemption->discount->value }}%
                                        @else
                                            €{{ number_format($discountRedemption->discount->value, 2) }}
                                        @endif
                                    </dd>
                                </div>

                                @if($discountRedemption->discount->description)
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            {{ __('frontend.discounts.fields.description') }}
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                            {{ $discountRedemption->discount->description }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ __('frontend.discount_redemptions.show.quick_actions') }}
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <a href="{{ route('frontend.discount-redemptions.index') }}" 
                               class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 text-center block">
                                {{ __('frontend.discount_redemptions.actions.back_to_list') }}
                            </a>
                            
                            <a href="{{ route('frontend.discount-redemptions.create') }}" 
                               class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 text-center block">
                                {{ __('frontend.discount_redemptions.actions.create_new') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Technical Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ __('frontend.discount_redemptions.show.technical_info') }}
                        </h2>
                    </div>
                    <div class="p-6">
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('frontend.discount_redemptions.fields.id') }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">
                                    #{{ $discountRedemption->id }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('frontend.discount_redemptions.fields.updated_at') }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $discountRedemption->updated_at->format('d.m.Y H:i:s') }}
                                </dd>
                            </div>

                            @if($discountRedemption->ip_address)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('frontend.discount_redemptions.fields.ip_address') }}
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">
                                        {{ $discountRedemption->ip_address }}
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

