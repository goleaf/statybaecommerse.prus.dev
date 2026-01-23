<div class="space-y-10">
    <x-breadcrumbs :items="[
        ['label' => __('frontend.account.nav.title'), 'url' => route('account.index', ['locale' => app()->getLocale()])],
        ['label' => __('frontend.account.orders.title')],
    ]" />
    <x-page-heading
                    :title="__('frontend.account.orders.title')"
                    :description="__('frontend.account.orders.description')" />
    @if ($orders->isEmpty())
        <div class="flex flex-col items-center py-6 space-y-5">
            <x-untitledui-shopping-bag
                                       class="size-12 text-gray-400"
                                       stroke-width="1"
                                       aria-hidden="true" />
            <p class="max-w-3xl mx-auto text-sm text-gray-500">
                {{ __('frontend.account.orders.empty_message') }}
            </p>
            <x-buttons.primary :href="route('home', ['locale' => app()->getLocale()])" class="px-4 text-sm">
                {{ __('frontend.account.orders.continue_shopping') }}
            </x-buttons.primary>
        </div>
    @else
        <div class="divide-y divide-gray-200">
            @foreach ($orders as $order)
                <x-order :order="$order" />
            @endforeach
        </div>

        <div class="lg:max-w-4xl">
            {{ $orders->links() }}
        </div>
    @endif
</div>
