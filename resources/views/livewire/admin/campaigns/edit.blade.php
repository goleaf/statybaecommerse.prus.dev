@extends('components.layouts.base', ['title' => $id ? __('Edit Campaign') : __('Create Campaign')])

@section('content')
    <x-container class="py-10">
        <h1 class="text-2xl font-semibold mb-6">{{ $id ? __('Edit Campaign') : __('Create Campaign') }}</h1>
        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('Name') }}</label>
                    <input type="text" wire:model="name" class="w-full border-gray-300" required />
                    @error('name')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('Slug') }}</label>
                    <input type="text" wire:model="slug" class="w-full border-gray-300" required />
                    @error('slug')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('Status') }}</label>
                    <select wire:model="status" class="w-full border-gray-300">
                        @foreach (['active', 'scheduled', 'inactive'] as $s)
                            <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('Starts at') }}</label>
                    <input type="datetime-local" wire:model="starts_at" class="w-full border-gray-300" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('Ends at') }}</label>
                    <input type="datetime-local" wire:model="ends_at" class="w-full border-gray-300" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('Zone') }}</label>
                    <select wire:model="zone_id" class="w-full border-gray-300">
                        <option value="">—</option>
                        @foreach ($zones as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('Channel') }}</label>
                    <select wire:model="channel_id" class="w-full border-gray-300">
                        <option value="">—</option>
                        @foreach ($channels as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Discounts') }}</label>
                <div class="border rounded p-3 max-h-80 overflow-y-auto">
                    @foreach ($discounts as $d)
                        <label class="block text-sm">
                            <input type="checkbox" value="{{ $d['id'] }}" wire:model="discount_ids" />
                            #{{ $d['id'] }} — {{ $d['type'] }} {{ $d['value'] }} @if (!empty($d['code']))
                                ({{ $d['code'] }})
                            @endif
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3">
                <button class="px-4 py-2 bg-primary-600 text-white rounded"
                        wire:loading.attr="disabled">{{ __('Save') }}</button>
                <a href="{{ route('admin.campaigns.index') }}" class="px-4 py-2 border rounded">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-container>
@endsection
