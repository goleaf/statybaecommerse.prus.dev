<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.templates.account')] class extends Component {
    public $reviews;

    public function mount(): void
    {
        $user = auth()->user();
        $this->reviews = collect();

        if ($user) {
            $this->reviews = \App\Models\Review::query()->where('user_id', $user->id)->latest()->limit(200)->get();
        }
    }
}; ?>

<div class="space-y-10">
    <x-breadcrumbs :items="[["label"=> __("My account"), "url" => route('account')], ["label" => __("My
        reviews")]]" />
        <x-page-heading :title="__('My reviews')" />

        @if ($reviews->isEmpty())
            <p class="text-gray-500">{{ __('You have not posted any reviews yet.') }}</p>
        @else
            <div class="divide-y divide-gray-200 rounded-md border border-gray-200">
                @foreach ($reviews as $r)
                    <div class="p-4 space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-yellow-500">{{ str_repeat('★', (int) ($r->rating ?? 0)) }}</span>
                            <span class="text-sm text-gray-500">{{ $r->created_at }}</span>
                        </div>
                        <div class="font-medium">{{ $r->title ?? __('Review') }}</div>
                        <div class="text-sm text-gray-700">{{ $r->content ?? '' }}</div>
                    </div>
                @endforeach
            </div>
        @endif
</div>

{
"cells": [],
"metadata": {
"language_info": {
"name": "python"
}
},
"nbformat": 4,
"nbformat_minor": 2
}
