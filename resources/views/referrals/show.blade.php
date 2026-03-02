<x-layouts.templates.account title="{{ __('referrals.show.title') }}">
    <div class="space-y-6">
        <header class="border-b border-gray-200 pb-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <a href="{{ route('referrals.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-200 p-2 text-gray-500 hover:bg-gray-50 hover:text-gray-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ __('referrals.show.title') }}</h1>
                        <p class="mt-1 text-sm text-gray-600">{{ __('referrals.show.description') }}</p>
                    </div>
                </div>

                @php
                    $statusLabel = match ($referral->status) {
                        'completed' => __('referrals.index.status.completed'),
                        'pending' => __('referrals.index.status.pending'),
                        'expired' => __('referrals.show.status.expired'),
                        default => ucfirst((string) $referral->status),
                    };

                    $statusClass = match ($referral->status) {
                        'completed' => 'bg-green-100 text-green-800',
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'expired' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-700',
                    };
                @endphp

                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $statusClass }}">
                    {{ $statusLabel }}
                </span>
            </div>
        </header>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-lg border border-gray-200 p-6">
                <h2 class="border-b border-gray-100 pb-3 text-base font-semibold text-gray-900">{{ __('referrals.show.information_title') }}</h2>
                <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('referrals.show.labels.code') }}</dt>
                        <dd class="mt-1 inline-block rounded bg-gray-100 px-2 py-1 font-mono text-sm text-gray-900">{{ $referral->referral_code }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('referrals.show.labels.created_on') }}</dt>
                        <dd class="mt-1 text-sm text-gray-800">{{ $referral->created_at->format('M d, Y') }}</dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('referrals.show.labels.referred_friend') }}</dt>
                        <dd class="mt-2">
                            @if ($referral->referred)
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 font-semibold text-primary-700">
                                        {{ substr($referral->referred->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $referral->referred->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $referral->referred->email }}</p>
                                    </div>
                                </div>
                            @else
                                <p class="text-sm italic text-gray-500">{{ __('referrals.show.labels.user_not_registered') }}</p>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg border border-gray-200 p-6">
                <h2 class="flex items-center justify-between border-b border-gray-100 pb-3 text-base font-semibold text-gray-900">
                    <span>{{ __('referrals.show.rewards_title') }}</span>
                    <span class="text-sm text-primary-700">{{ __('referrals.show.total_label') }}: €{{ number_format($referral->rewards->sum('amount'), 2) }}</span>
                </h2>

                @if ($referral->rewards->count() > 0)
                    <div class="mt-4 space-y-3">
                        @foreach ($referral->rewards as $reward)
                            <div class="flex items-center justify-between rounded-md border border-gray-200 px-4 py-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $reward->type ?? __('referrals.show.reward_type_default') }}</p>
                                    <p class="text-xs text-gray-500">{{ ucfirst((string) $reward->status) }}</p>
                                </div>
                                <p class="text-sm font-semibold text-gray-900">€{{ number_format($reward->amount, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm text-gray-500">{{ __('referrals.show.no_rewards') }}</p>
                @endif
            </div>
        </div>
    </div>
</x-layouts.templates.account>
