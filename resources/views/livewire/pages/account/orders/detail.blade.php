{{-- Order Detail Component --}}

<div>
    <x-breadcrumbs :items="[
        ['label' => __('ui.messages_frontend'), 'url' => route('account.index')],
        ['label' => __('ui.messages_frontend'), 'url' => route('account.orders')],
        ['label' => __('frontend.account.order_detail.title')],
    ]" />
    <h1 class="text-xl font-semibold text-gray-900 font-heading lg:text-2xl">
        {{ __('frontend.account.order_detail.title') }}
    </h1>
    <div class="flex flex-col mt-6 space-y-10 lg:space-y-14">
        <div class="flex items-center justify-between px-4 py-2 bg-gray-50 lg:max-w-5xl">
            <div class="text-sm">
                <dt class="font-medium tracking-tighter text-gray-700">
                    {{ __('frontend.account.order_detail.order_number_label') }}
                </dt>
                <dd class="ml-1.5 font-medium uppercase text-gray-500">
                    {{ __('frontend.account.order_detail.order_number_value', ['number' => $order->number]) }}
                </dd>
            </div>
            <div class="text-sm">
                <dt class="font-medium tracking-tighter text-gray-900">
                    {{ __('frontend.account.order_detail.placed_on') }}
                </dt>
                <dd class="mt-1 text-gray-500 capitalize">
                    <time datetime="{{ format_datetime($order->created_at) }}">
                        {{ format_datetime($order->created_at) }}
                    </time>
                </dd>
            </div>
            <div class="text-sm">
                <dt class="font-medium tracking-tighter text-gray-900">
                    {{ __('frontend.account.order_detail.total') }}
                </dt>
                <dd class="mt-1 text-gray-500">
                    {{ app_money_format($order->total ?? 0.0, $order->currency) }}
                </dd>
            </div>
            <div class="text-sm">
                <dt class="font-medium tracking-tighter text-gray-900">{{ __('frontend.account.order_detail.status') }}</dt>
                <dd class="mt-1 text-gray-500">
                    <x-order.status :status="$order->status" />
                </dd>
            </div>
        </div>

        <x-order.items :items="$order->items" :currency_code="$order->currency" />

        <div class="max-w-xl">
            <div class="flex items-end justify-end">
                <h6 class="bg-brand inline-flex w-auto px-2.5 py-1 text-sm leading-6 text-white">
                    {{ __('frontend.account.order_detail.summary') }}
                </h6>
            </div>
            <x-order.summary :order="$order" />
        </div>
    </div>
    <div class="max-w-md mt-10 lg:mt-20">
        <p class="mt-5 text-sm leading-5 text-gray-500">
            {{ __('frontend.account.order_detail.help_message') }}
        </p>
        <div class="mt-4">
            <x-buttons.default href="/" class="px-4">
                {{ __('frontend.account.order_detail.contact_us') }}
            </x-buttons.default>
        </div>
    </div>
</div>
