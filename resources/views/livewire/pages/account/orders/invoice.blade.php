<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.templates.account');
title(__('Invoice'));

?>

<div class="space-y-10">
    <x-breadcrumbs :items="[
        ['label' => __('My account'), 'url' => route('account.index', ['locale' => app()->getLocale()])],
        ['label' => __('My orders'), 'url' => route('account.orders', ['locale' => app()->getLocale()])],
        ['label' => __('Invoice')],
    ]" />

    <div class="flex items-center justify-end">
        <x-buttons.default type="button" class="px-4 print:hidden" onclick="window.print()">
            {{ __('Print') }}
        </x-buttons.default>
    </div>

    <x-order.invoice :order="$order" />
</div>
