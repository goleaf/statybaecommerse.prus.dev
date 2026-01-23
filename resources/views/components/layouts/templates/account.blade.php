<x-layouts.base :title="$title ?? null">
    <x-container class="relative py-8 sm:py-12 lg:py-16">
        <div class="grid grid-cols-1 lg:grid-cols-5 lg:gap-x-12">
            <div class="lg:col-span-1">
                <h2 class="hidden text-xl font-medium leading-6 text-gray-900 font-heading lg:block">
                    {{ __('frontend.account.nav.title') }}
                </h2>
                <div class="hidden mt-10 space-y-8 lg:block">
                    <nav role="navigation" class="flex flex-col space-y-4 lg:pr-12">
                        <x-nav.account-link
                                            :href="route('account.index')"
                                            :title="__('frontend.account.nav.overview')"
                                            :active="request()->routeIs('account')" />
                        <x-nav.account-link
                                            :href="route('account.profile')"
                                            :title="__('frontend.account.nav.profile')"
                                            :active="request()->routeIs('account.profile')" />
                        <x-nav.account-link
                                            :href="route('account.addresses')"
                                            :title="__('frontend.account.nav.addresses')"
                                            :active="request()->routeIs('account.addresses')" />
                        <x-nav.account-link
                                            :href="route('account.orders')"
                                            :title="__('frontend.account.nav.orders')"
                                            :active="request()->routeIs('account.orders*')" />
                        <x-nav.account-link
                                            :href="route('account.reviews')"
                                            :title="__('frontend.account.nav.reviews')"
                                            :active="request()->routeIs('account.reviews')" />
                        <x-nav.account-link
                                            :href="route('account.wishlist')"
                                            :title="__('frontend.account.nav.wishlist')"
                                            :active="request()->routeIs('account.wishlist')" />
                        <x-nav.account-link
                                            :href="route('account.documents')"
                                            :title="__('frontend.account.nav.documents')"
                                            :active="request()->routeIs('account.documents')" />
                        <x-nav.account-link
                                            :href="route('account.notifications')"
                                            :title="__('frontend.account.nav.notifications')"
                                            :active="request()->routeIs('account.notifications')" />
                    </nav>
                </div>
            </div>
            <div class="lg:col-span-4">{{ $slot }}</div>
        </div>
    </x-container>
</x-layouts.base>
