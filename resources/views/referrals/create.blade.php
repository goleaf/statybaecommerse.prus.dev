<x-layouts.templates.account title="{{ __('messages.referrals') }}">
    <div class="space-y-6">
        <header class="border-b border-gray-200 dark:border-white/10 pb-5 mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('referrals.index') }}" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-300 transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Invite a Friend</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Send an invitation to earn rewards together.</p>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form -->
            <div class="lg:col-span-2">
                <form action="{{ route('referrals.store') }}" method="POST" class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-6 border border-gray-100 dark:border-white/5 shadow-sm space-y-6">
                    @csrf
                    
                    <div>
                        <label for="referred_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Friend's Email Address <span class="text-red-500">*</span>
                        </label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                            </div>
                            <input type="email" id="referred_email" name="referred_email" value="{{ old('referred_email') }}" required
                                class="pl-10 block w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="friend@example.com">
                        </div>
                        @error('referred_email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Invitation Note (Optional)
                        </label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Check out this great store!">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Personal Message (Optional)
                        </label>
                        <textarea id="description" name="description" rows="3"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="I thought you might like some of the products here. Use my link to sign up.">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-4 flex items-center gap-3 border-t border-gray-200 dark:border-white/10">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="terms" name="terms" type="checkbox" required
                                    class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded dark:border-gray-600 dark:bg-gray-700">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="terms" class="font-medium text-gray-700 dark:text-gray-300">I agree to the <a href="{{ route('frontend.legal.terms') }}" class="text-primary-600 dark:text-primary-400 hover:underline">Referral Terms</a></label>
                            </div>
                        </div>
                        @error('terms')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="inline-flex justify-center items-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                            Send Invitation
                        </button>
                    </div>
                </form>
            </div>

            <!-- Side Info -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-primary-50 dark:bg-primary-900/20 rounded-xl p-6 border border-primary-100 dark:border-primary-800/30">
                    <h3 class="text-base font-semibold text-primary-900 dark:text-primary-100 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                        Why Refer?
                    </h3>
                    <ul class="space-y-3 text-sm text-primary-800 dark:text-primary-200">
                        <li class="flex items-start">
                            <svg class="h-5 w-5 mr-2 shrink-0 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            <span>Earn store credit for every friend who signs up and makes a purchase.</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 mr-2 shrink-0 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            <span>Your friends get an exclusive discount on their first order.</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 mr-2 shrink-0 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            <span>No limits on how many friends you can invite.</span>
                        </li>
                    </ul>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-white/10 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                        </svg>
                        How It Works
                    </h3>
                    <ol class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
                        <li class="flex gap-3">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center font-medium text-xs text-gray-900 dark:text-white">1</div>
                            <span>You enter your friend's email and send the invitation.</span>
                        </li>
                        <li class="flex gap-3">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center font-medium text-xs text-gray-900 dark:text-white">2</div>
                            <span>They receive an email with your unique link.</span>
                        </li>
                        <li class="flex gap-3">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center font-medium text-xs text-gray-900 dark:text-white">3</div>
                            <span>Once they complete a purchase, your reward is added automatically.</span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</x-layouts.templates.account>
