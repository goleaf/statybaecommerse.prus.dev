<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.templates.account');
title(__('Invoice'));

?>

<div class="space-y-10">
    <x-breadcrumbs :items="[
        ['label' => __('My account'), 'url' => route('account.index', [])],
        ['label' => __('My orders'), 'url' => route('account.orders', [])],
        ['label' => __('Invoice')],
    ]" />

    <div class="flex items-center justify-end">
        <x-buttons.default type="button" class="px-4 print:hidden" onclick="window.print()">
            {{ __('Print') }}
        </x-buttons.default>
    </div>

    <style>
        @media print {

            header,
            footer,
            nav,
            .print\:hidden {
                display: none !important;
            }

            body {
                background: white !important;
            }
        }
    </style>

    <x-order.invoice :order="$order" />
</div>
