<x-layouts.templates.account title="{{ __('referrals.frontend.my_rewards') }}">
    @php
        $summary = [
            'total_rewards' => (float) data_get($stats ?? [], 'total_rewards', $totalRewards ?? 0),
            'pending_rewards' => (float) data_get($stats ?? [], 'pending_rewards', $pendingRewards ?? 0),
            'applied_rewards' => (float) data_get($stats ?? [], 'applied_rewards', $appliedRewards ?? 0),
        ];
    @endphp

    <div class="space-y-6">
        <header class="border-b border-gray-200 pb-5">
            <div class="flex items-center gap-3">
                <a href="{{ route('referrals.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-200 p-2 text-gray-500 hover:bg-gray-50 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ __('referrals.frontend.my_rewards') }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ __('referrals.frontend.rewards_description') }}</p>
                </div>
            </div>
        </header>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-lg border border-primary-100 bg-primary-50 p-5">
                <dt class="text-sm font-medium text-primary-800">{{ __('referrals.statistics.total_amount') }}</dt>
                <dd class="mt-1 text-2xl font-semibold text-primary-900">€{{ number_format($summary['total_rewards'], 2) }}</dd>
            </div>

            <div class="rounded-lg border border-yellow-100 bg-yellow-50 p-5">
                <dt class="text-sm font-medium text-yellow-800">{{ __('referrals.pending_rewards') }}</dt>
                <dd class="mt-1 text-2xl font-semibold text-yellow-900">€{{ number_format($summary['pending_rewards'], 2) }}</dd>
            </div>

            <div class="rounded-lg border border-green-100 bg-green-50 p-5">
                <dt class="text-sm font-medium text-green-800">{{ __('referrals.forms.applied_at') }}</dt>
                <dd class="mt-1 text-2xl font-semibold text-green-900">€{{ number_format($summary['applied_rewards'], 2) }}</dd>
            </div>
        </dl>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                <h2 class="text-base font-semibold text-gray-900">{{ __('referrals.frontend.my_rewards') }}</h2>
            </div>

            @if ($rewards->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('referrals.forms.referral_code') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('referrals.index.table.user') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('referrals.forms.reward_details') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('referrals.status') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('referrals.statistics.total_amount') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('referrals.forms.created_at') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($rewards as $reward)
                                @php
                                    $statusClass = match ($reward->status) {
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'applied' => 'bg-green-100 text-green-800',
                                        'expired' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-700',
                                    };

                                    $statusLabel = match ($reward->status) {
                                        'pending' => __('referrals.index.status.pending'),
                                        'applied' => __('referrals.forms.applied_at'),
                                        'expired' => __('referrals.show.status.expired'),
                                        default => ucfirst((string) $reward->status),
                                    };
                                @endphp

                                <tr class="transition-colors hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        @if ($reward->referral)
                                            <a href="{{ route('referrals.show', $reward->referral) }}" class="font-mono text-primary-700 hover:text-primary-800">
                                                {{ $reward->referral->referral_code ?? '—' }}
                                            </a>
                                        @else
                                            <span class="font-mono text-gray-500">—</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-800">
                                        {{ $reward->referral?->referred?->name ?? __('referrals.not_registered_yet') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        @if ($reward->type === 'referrer_bonus')
                                            {{ __('referrals.types.referrer_bonus') }}
                                        @elseif ($reward->type === 'referred_discount')
                                            {{ __('referrals.types.referred_discount') }}
                                        @else
                                            {{ $reward->localized_title ?: ucfirst((string) $reward->type) }}
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                        €{{ number_format((float) $reward->amount, 2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ $reward->created_at?->format('Y-m-d H:i') ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($rewards->hasPages())
                    <div class="border-t border-gray-200 px-6 py-4">
                        {{ $rewards->links() }}
                    </div>
                @endif
            @else
                <div class="px-6 py-12 text-center">
                    <h3 class="text-base font-medium text-gray-900">{{ __('referrals.frontend.no_rewards_yet') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('referrals.frontend.start_referring') }}</p>
                    <div class="mt-6">
                        <a href="{{ route('referrals.create') }}" class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                            {{ __('referrals.frontend.start_referring') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.templates.account>
