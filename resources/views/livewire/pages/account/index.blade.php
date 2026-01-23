{{-- Account Index Page --}}
@php
// Build links without Volt computed to avoid test-time Volt closure issues
$links = [
    [
        'title' => __('frontend.account.orders.title'),
        'description' => __('frontend.account.cards.orders.description'),
        'href' => route('account.orders'),
        'icon' => 'untitledui-shopping-bag',
    ],
    [
        'title' => __('frontend.account.reviews'),
        'description' => __('frontend.account.cards.reviews.description'),
        'href' => route('account.reviews'),
        'icon' => 'untitledui-star-07',
    ],
    [
        'title' => __('frontend.account.cards.profile.title'),
        'description' => __('frontend.account.cards.profile.description'),
        'href' => route('account.profile'),
        'icon' => 'untitledui-shield-tick',
    ],
    [
        'title' => __('frontend.account.cards.addresses.title'),
        'description' => __('frontend.account.cards.addresses.description'),
        'href' => route('account.addresses'),
        'icon' => 'untitledui-globe-05',
    ],
    [
        'title' => __('frontend.account.wishlist'),
        'description' => __('frontend.account.cards.wishlist.description'),
        'href' => route('account.wishlist'),
        'icon' => 'untitledui-heart',
    ],
    [
        'title' => __('frontend.account.documents'),
        'description' => __('frontend.account.cards.documents.description'),
        'href' => route('account.documents'),
        'icon' => 'untitledui-file-02',
    ],
    [
        'title' => __('frontend.account.notifications'),
        'description' => __('frontend.account.cards.notifications.description'),
        'href' => route('account.notifications'),
        'icon' => 'untitledui-bell-01',
    ],
    [
        'title' => __('frontend.account.cards.contact.title'),
        'description' => __('frontend.account.cards.contact.description'),
        'href' => route('account.index'),
        'icon' => 'untitledui-phone',
    ],
];
@endphp

<div class="space-y-10">
    <x-breadcrumbs :items="[['label' => __('frontend.account.nav.title')]]" />
    <x-page-heading
                    :title="__('frontend.account.overview.title')"
                    :description="__('frontend.account.overview.description')" />

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:max-w-5xl lg:grid-cols-3">
        @foreach ($links as $link)
            <x-account-card-link
                                 :href="$link['href']"
                                 :title="$link['title']"
                                 :description="$link['description']"
                                 :icon="$link['icon']" />
        @endforeach
    </div>
</div>
