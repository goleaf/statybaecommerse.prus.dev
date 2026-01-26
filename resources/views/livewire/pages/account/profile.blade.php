{{-- Account Profile Component --}}

<div class="space-y-10">
    <x-breadcrumbs :items="[['label' => __('messages.frontend), 'url' => route('account.index')], ['label' => __('messages.frontend)]]" />
    <x-page-heading :title="__('messages.frontend)" />

    <div class="space-y-6 divide-y divide-gray-200">
        <div class="sm:grid sm:grid-cols-2 sm:gap-6">
            <livewire:components.profile.update-profile-information-form />
            <livewire:components.profile.update-password-form />
        </div>
        <livewire:components.profile.delete-user-form />
    </div>
</div>
