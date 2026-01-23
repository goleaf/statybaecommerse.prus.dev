{{-- Account Profile Component --}}

<div class="space-y-10">
    <x-breadcrumbs :items="[['label' => __('frontend.account.nav.title'), 'url' => route('account.index')], ['label' => __('frontend.account.profile')]]" />
    <x-page-heading :title="__('frontend.account.profile')" />

    <div class="space-y-6 divide-y divide-gray-200">
        <div class="sm:grid sm:grid-cols-2 sm:gap-6">
            <livewire:components.profile.update-profile-information-form />
            <livewire:components.profile.update-password-form />
        </div>
        <livewire:components.profile.delete-user-form />
    </div>
</div>
