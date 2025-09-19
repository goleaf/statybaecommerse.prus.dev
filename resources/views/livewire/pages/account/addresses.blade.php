<div class="space-y-10">
    <x-breadcrumbs :items="[
        ['label' => __('My account'), 'url' => route('account.index', ['locale' => app()->getLocale()])],
        ['label' => __('Addresses')],
    ]" />
    <x-page-heading
                    :title="__('My addresses')"
                    :description="__('View and update your delivery and billing addresses here.')" />

    <div class="space-y-8">
        <button
            type="button"
            wire:click="$dispatch('openModal', { component: 'modals.account.address-form' })"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 w-full sm:w-auto"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            {{ __('Add address') }}
        </button>

        @if ($addresses->isNotEmpty())
            <div class="sm:grid sm:grid-cols-2 sm:gap-6 lg:grid-cols-4">
                @foreach ($addresses as $address)
                    <x-address.edit-address :$address />
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500">
                {{ __('You have not yet added any addresses to your space.') }}
            </p>
        @endif
    </div>
</div>
