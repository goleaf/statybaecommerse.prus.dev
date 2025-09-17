<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.templates.account');
title(__('Profile'));

?>

<div class="space-y-10">
    <x-breadcrumbs :items="[['label' => __('My account'), 'url' => route('account.index')], ['label' => __('Profile')]]" />
    <x-page-heading :title="__('Profile')" />

    <div class="space-y-6 divide-y divide-gray-200">
        <div class="sm:grid sm:grid-cols-2 sm:gap-6">
            <livewire:components.profile.update-profile-information-form />
            <livewire:components.profile.update-password-form />
        </div>
        <livewire:components.profile.delete-user-form />
    </div>
</div>
