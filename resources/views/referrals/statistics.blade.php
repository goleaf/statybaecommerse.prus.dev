<x-layouts.templates.account title="{{ __('referrals.index.actions.statistics') }}">
    <div class="space-y-6">
        <header class="border-b border-gray-200 pb-5">
            <div class="flex items-center gap-3">
                <a href="{{ route('referrals.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-200 p-2 text-gray-500 hover:bg-gray-50 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ __('referrals.index.actions.statistics') }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ __('referrals.index.description') }}</p>
                </div>
            </div>
        </header>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-blue-100 bg-blue-50 p-5">
                <dt class="text-sm font-medium text-blue-800">{{ __('referrals.total_referrals') }}</dt>
                <dd class="mt-1 text-2xl font-semibold text-blue-900">{{ (int) ($totalReferrals ?? 0) }}</dd>
            </div>

            <div class="rounded-lg border border-green-100 bg-green-50 p-5">
                <dt class="text-sm font-medium text-green-800">{{ __('referrals.completed_referrals') }}</dt>
                <dd class="mt-1 text-2xl font-semibold text-green-900">{{ (int) ($completedReferrals ?? 0) }}</dd>
            </div>

            <div class="rounded-lg border border-yellow-100 bg-yellow-50 p-5">
                <dt class="text-sm font-medium text-yellow-800">{{ __('referrals.pending_referrals') }}</dt>
                <dd class="mt-1 text-2xl font-semibold text-yellow-900">{{ (int) ($pendingReferrals ?? 0) }}</dd>
            </div>

            <div class="rounded-lg border border-red-100 bg-red-50 p-5">
                <dt class="text-sm font-medium text-red-800">{{ __('referrals.show.status.expired') }}</dt>
                <dd class="mt-1 text-2xl font-semibold text-red-900">{{ (int) ($expiredReferrals ?? 0) }}</dd>
            </div>
        </dl>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-lg border border-primary-100 bg-primary-50 p-5">
                <dt class="text-sm font-medium text-primary-800">{{ __('referrals.statistics.total_amount') }}</dt>
                <dd class="mt-1 text-2xl font-semibold text-primary-900">€{{ number_format((float) ($totalRewards ?? 0), 2) }}</dd>
            </div>

            <div class="rounded-lg border border-yellow-100 bg-yellow-50 p-5">
                <dt class="text-sm font-medium text-yellow-800">{{ __('referrals.pending_rewards') }}</dt>
                <dd class="mt-1 text-2xl font-semibold text-yellow-900">€{{ number_format((float) ($pendingRewards ?? 0), 2) }}</dd>
            </div>

            <div class="rounded-lg border border-green-100 bg-green-50 p-5">
                <dt class="text-sm font-medium text-green-800">{{ __('referrals.forms.applied_at') }}</dt>
                <dd class="mt-1 text-2xl font-semibold text-green-900">€{{ number_format((float) ($appliedRewards ?? 0), 2) }}</dd>
            </div>
        </dl>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                <h2 class="text-base font-semibold text-gray-900">{{ __('referrals.index.list_title') }}</h2>
            </div>

            @if (($monthlyStats ?? collect())->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('referrals.index.table.date') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('referrals.total_referrals') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($monthlyStats as $stat)
                                <tr class="transition-colors hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ sprintf('%04d-%02d', (int) $stat->year, (int) $stat->month) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ (int) $stat->count }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <h3 class="text-base font-medium text-gray-900">{{ __('referrals.index.empty.title') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('referrals.index.empty.description') }}</p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.templates.account>
