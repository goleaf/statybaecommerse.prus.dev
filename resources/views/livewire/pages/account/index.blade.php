{{-- Account Index Page --}}
@php
// Build links without Volt computed to avoid test-time Volt closure issues
$links = [
    [
        'title' => __('messages.frontend),
        'description' => __('messages.frontend),
        'href' => route('account.orders'),
        'icon' => 'untitledui-shopping-bag',
    ],
    [
        'title' => __('messages.frontend),
        'description' => __('messages.frontend),
        'href' => route('account.reviews'),
        'icon' => 'untitledui-star-07',
    ],
    [
        'title' => __('messages.frontend),
        'description' => __('messages.frontend),
        'href' => route('account.profile'),
        'icon' => 'untitledui-shield-tick',
    ],
    [
        'title' => __('messages.frontend),
        'description' => __('messages.frontend),
        'href' => route('account.addresses'),
        'icon' => 'untitledui-globe-05',
    ],
    [
        'title' => __('messages.frontend),
        'description' => __('messages.frontend),
        'href' => route('account.wishlist'),
        'icon' => 'untitledui-heart',
    ],
    [
        'title' => __('messages.frontend),
        'description' => __('messages.frontend),
        'href' => route('account.documents'),
        'icon' => 'untitledui-file-02',
    ],
    [
        'title' => __('messages.frontend),
        'description' => __('messages.frontend),
        'href' => route('account.notifications'),
        'icon' => 'untitledui-bell-01',
    ],
    [
        'title' => __('messages.frontend),
        'description' => __('messages.frontend),
        'href' => route('account.index'),
        'icon' => 'untitledui-phone',
    ],
];
@endphp

<div class="space-y-10">
    <x-breadcrumbs :items="[['label' => __('messages.frontend)]]" />
    <x-page-heading
                    :title="__('messages.frontend)"
                    :description="__('messages.frontend)" />

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
