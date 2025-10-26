@php
    use App\Enums\OrderStatus;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Number;
    use Illuminate\Support\Str;

    /**
     * Build the segment definitions once so the view can render tabs and translations consistently.
     *
     * @var array<string, string> $segments
     */
    $segments = [
        'open' => __('partners.dashboard.tabs.open'),
        'shipped' => __('partners.dashboard.tabs.shipped'),
        'cancelled' => __('partners.dashboard.tabs.cancelled'),
    ];

    /**
     * Tailwind utility classes keyed by the semantic color tokens emitted from the enum helper.
     */
    $badgeColorMap = [
        'primary' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'success' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'danger' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'secondary' => 'bg-slate-100 text-slate-800 dark:bg-slate-900 dark:text-slate-200',
    ];

    /**
     * Partner metadata extracted from the contract meta envelope.
     */
    $partnerMeta = Arr::get($orderPayload ?? [], 'meta.partner');

    /**
     * Orders in the contract payload – defaults to an empty array for guarded states.
     */
    $orders = Arr::get($orderPayload ?? [], 'data.orders', []);
@endphp

<x-layouts.base :title="__('partners.dashboard.title')">
    <div class="max-w-7xl mx-auto px-4 py-10 space-y-8">
        <header class="space-y-1">
            <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">
                {{ __('partners.dashboard.title') }}
            </h1>
            <p class="text-gray-600 dark:text-gray-300">
                {{ __('partners.dashboard.subtitle') }}
            </p>
            @if (is_array($partnerMeta))
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{-- Surface the resolved partner identity so the user understands the scope of the listing. --}}
                    {{ __('partners.models.partner') }}:
                    <span class="font-medium text-gray-900 dark:text-gray-100">
                        {{ $partnerMeta['name'] ?? $partnerMeta['code'] ?? __('orders.lookups.partner_unknown') }}
                    </span>
                    @if (! empty($partnerMeta['code']))
                        <span class="ml-2 text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                            {{ $partnerMeta['code'] }}
                        </span>
                    @endif
                </p>
            @endif
        </header>

        @if ($errorCode === \Illuminate\Http\Response::HTTP_FORBIDDEN)
            {{-- Guarded empty state for users without partner membership. --}}
            <div class="rounded-xl border border-dashed border-red-200 bg-red-50 p-6 text-center dark:border-red-900 dark:bg-red-950/40">
                <h2 class="text-lg font-semibold text-red-800 dark:text-red-200">
                    {{ __('partners.dashboard.errors.forbidden.title') }}
                </h2>
                <p class="mt-2 text-sm text-red-700 dark:text-red-300">
                    {{ __('partners.dashboard.errors.forbidden.description') }}
                </p>
            </div>
        @elseif ($errorCode === \Illuminate\Http\Response::HTTP_UNAUTHORIZED)
            {{-- Defensive message for unauthenticated requests – should rarely be hit thanks to middleware. --}}
            <div class="rounded-xl border border-dashed border-amber-200 bg-amber-50 p-6 text-center dark:border-amber-900 dark:bg-amber-950/40">
                <h2 class="text-lg font-semibold text-amber-800 dark:text-amber-200">
                    {{ __('partners.dashboard.errors.unauthorized.title') }}
                </h2>
                <p class="mt-2 text-sm text-amber-700 dark:text-amber-300">
                    {{ __('partners.dashboard.errors.unauthorized.description') }}
                </p>
            </div>
        @else
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="inline-flex rounded-lg bg-gray-100 p-1 dark:bg-gray-800">
                    {{-- Segmented control preserving query parameters when switching tabs. --}}
                    @foreach ($segments as $segmentKey => $segmentLabel)
                        @php
                            $isActive = $activeStatus === $segmentKey;
                            $query = array_merge(request()->query(), ['status' => $segmentKey, 'page' => 1]);
                            $url = route('frontend.partner.orders.index', $query);
                        @endphp
                        <a href="{{ $url }}"
                           class="rounded-md px-4 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-primary-500 {{ $isActive ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-gray-100' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white' }}">
                            {{ $segmentLabel }}
                        </a>
                    @endforeach
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{-- Display the active filter for extra clarity on the dataset shown. --}}
                    {{ __('partners.dashboard.tabs.' . $activeStatus) }} •
                    {{ __('partners.dashboard.result_count', ['count' => $paginator?->total() ?? 0]) }}
                </p>
            </div>

            @if (empty($orders))
                {{-- Friendly empty-state when the selected filter returns no results. --}}
                <div class="rounded-xl border border-gray-200 bg-white p-10 text-center shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('partners.dashboard.empty.title') }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        {{ __('partners.dashboard.empty.description') }}
                    </p>
                </div>
            @else
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        {{ __('partners.dashboard.table.order') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        {{ __('partners.dashboard.table.status') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        {{ __('partners.dashboard.table.payment_status') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        {{ __('partners.dashboard.table.items') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        {{ __('partners.dashboard.table.total') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        {{ __('partners.dashboard.table.placed_at') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                @foreach ($orders as $order)
                                    @php
                                        $statusState = (string) Arr::get($order, 'status.state', '');
                                        $statusEnum = OrderStatus::tryFrom($statusState);
                                        $statusLabel = $statusEnum?->label() ?? Str::headline($statusState);
                                        $statusColorKey = $statusEnum?->getColor();
                                        $statusBadgeClasses = $badgeColorMap[$statusColorKey] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';

                                        $paymentState = (string) Arr::get($order, 'status.payment_state', '');
                                        $paymentLabel = __('orders.payment_statuses.' . $paymentState);
                                        if ($paymentLabel === 'orders.payment_statuses.' . $paymentState) {
                                            $paymentLabel = Str::headline($paymentState);
                                        }

                                        $currency = (string) Arr::get($order, 'totals.currency', current_currency());
                                        $orderTotal = (float) Arr::get($order, 'totals.total', 0);
                                        $itemCount = count(Arr::get($order, 'items', []));
                                        $placedAt = Arr::get($order, 'placed_at');
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            <div>{{ Arr::get($order, 'number') }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">#{{ Arr::get($order, 'id') }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadgeClasses }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                            {{ $paymentLabel }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                            {{ trans_choice('partners.dashboard.table.items_count', $itemCount, ['count' => $itemCount]) }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ Number::currency($orderTotal, $currency, app()->getLocale()) }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                            @if ($placedAt)
                                                <time datetime="{{ $placedAt }}">{{ \Illuminate\Support\Carbon::parse($placedAt)->translatedFormat('Y-m-d H:i') }}</time>
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                    {{-- Preserve filters when paginating through the dataset. --}}
                    <div class="mt-6">
                        {{ $paginator->appends(request()->query())->links() }}
                    </div>
                @endif
            @endif
        @endif
    </div>
</x-layouts.base>
