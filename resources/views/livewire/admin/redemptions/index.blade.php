@extends('components.layouts.base', ['title' => __('Discount Redemptions')])

@section('content')
    <x-container class="py-10">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">{{ __('Discount Redemptions') }}</h1>
            <button wire:click="exportCsv" class="px-4 py-2 bg-primary-600 text-white rounded">{{ __('Export CSV') }}</button>
        </div>
        <form wire:submit.prevent="load" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
            <div>
                <label class="block text-sm">{{ __('Discount') }}</label>
                <select wire:model="discount_id" class="w-full border-gray-300">
                    <option value="">—</option>
                    @foreach ($discounts as $d)
                        <option value="{{ $d['id'] }}">#{{ $d['id'] }} {{ $d['code'] }} ({{ $d['type'] }}
                            {{ $d['value'] }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm">{{ __('User ID') }}</label>
                <input type="number" wire:model="user_id" class="w-full border-gray-300" />
            </div>
            <div>
                <label class="block text-sm">{{ __('From') }}</label>
                <input type="date" wire:model="from" class="w-full border-gray-300" />
            </div>
            <div>
                <label class="block text-sm">{{ __('To') }}</label>
                <input type="date" wire:model="to" class="w-full border-gray-300" />
            </div>
            <div class="flex items-end">
                <button class="px-4 py-2 bg-primary-600 text-white rounded">{{ __('Filter') }}</button>
            </div>
        </form>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left border-b">
                        <th class="py-2 pr-4">#</th>
                        <th class="py-2 pr-4">{{ __('Discount') }}</th>
                        <th class="py-2 pr-4">{{ __('User') }}</th>
                        <th class="py-2 pr-4">{{ __('Order') }}</th>
                        <th class="py-2 pr-4">{{ __('Amount Saved') }}</th>
                        <th class="py-2 pr-4">{{ __('Redeemed At') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $r)
                        <tr class="border-b">
                            <td class="py-2 pr-4">{{ $r['id'] }}</td>
                            <td class="py-2 pr-4">{{ $r['discount_code'] }} ({{ $r['discount_type'] }})</td>
                            <td class="py-2 pr-4">{{ $r['user_email'] ?? $r['user_id'] }}</td>
                            <td class="py-2 pr-4">{{ $r['order_id'] }}</td>
                            <td class="py-2 pr-4">
                                {{ shopper_money_format(amount: $r['amount_saved'], currency: $r['currency_code']) }}</td>
                            <td class="py-2 pr-4">{{ $r['redeemed_at'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-container>
@endsection
