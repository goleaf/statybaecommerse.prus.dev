<x-layouts.templates.account title="{{ __('messages.referrals') }}">
    <div class="space-y-6">
        <!-- Header -->
        <header class="border-b border-gray-200 dark:border-white/10 pb-5 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('referrals.index') }}" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-300 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Referral Details</h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">View information about this specific referral.</p>
                    </div>
                </div>
                
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($referral->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                    @elseif($referral->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                    @elseif($referral->status === 'expired') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                    @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 @endif
                ">
                    {{ ucfirst($referral->status) }}
                </span>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Referral Details -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-white/10 shadow-sm space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white pb-3 border-b border-gray-100 dark:border-white/5">Information</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Code</dt>
                        <dd class="text-sm font-mono bg-gray-50 dark:bg-gray-700 px-2 py-1 rounded inline-block text-gray-900 dark:text-white">{{ $referral->referral_code }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Created On</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">{{ $referral->created_at->format('M d, Y') }}</dd>
                    </div>

                    <div class="col-span-2">
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Referred Friend</dt>
                        <dd class="text-sm flex items-center gap-3 mt-2">
                            @if($referral->referred)
                                <div class="h-8 w-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-700 dark:text-primary-300 font-bold">
                                    {{ substr($referral->referred->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $referral->referred->name }}</div>
                                    <div class="text-gray-500 dark:text-gray-400 text-xs">{{ $referral->referred->email }}</div>
                                </div>
                            @else
                                <div class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </div>
                                <span class="text-gray-500 dark:text-gray-400 italic">User hasn't registered yet</span>
                            @endif
                        </dd>
                    </div>
                </div>
            </div>

            <!-- Rewards -->
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-6 border border-gray-100 dark:border-white/5 shadow-sm">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white pb-3 border-b border-gray-200 dark:border-white/10 mb-4 flex justify-between items-center">
                    <span>Rewards Earned</span>
                    <span class="text-sm font-semibold text-primary-600 dark:text-primary-400">Total: €{{ number_format($referral->rewards->sum('amount'), 2) }}</span>
                </h3>
                
                @if($referral->rewards->count() > 0)
                    <div class="space-y-3">
                        @foreach($referral->rewards as $reward)
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-white/10 shadow-sm flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-md">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $reward->type ?? 'Store Credit' }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst($reward->status) }}</div>
                                    </div>
                                </div>
                                <div class="font-semibold text-gray-900 dark:text-white">
                                    €{{ number_format($reward->amount, 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">No rewards earned from this referral yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.templates.account>
