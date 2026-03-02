<x-layouts.templates.account title="{{ __('referrals.share.title') }}">
    <div class="space-y-6">
        <header class="border-b border-gray-200 pb-5">
            <div class="flex items-center gap-3">
                <a href="{{ route('referrals.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-200 p-2 text-gray-500 hover:bg-gray-50 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ __('referrals.share.title') }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ __('referrals.share.description') }}</p>
                </div>
            </div>
        </header>

        <div class="rounded-lg border border-gray-200 p-6 text-center">
            <h2 class="text-base font-semibold text-gray-900">{{ __('referrals.share.code_title') }}</h2>
            <div class="mt-4 inline-flex items-center rounded-lg border border-primary-200 bg-primary-50 px-6 py-3">
                <span class="font-mono text-2xl font-bold tracking-wider text-primary-700">{{ $referralCode->code }}</span>
            </div>
            <p class="mx-auto mt-4 max-w-xl text-sm text-gray-600">{{ __('referrals.share.code_description') }}</p>
            <button type="button" onclick="copyCode()" class="mt-4 inline-flex items-center rounded-md bg-primary-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-700">
                {{ __('referrals.share.copy_code') }}
            </button>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-lg border border-gray-200 p-6">
                <h2 class="text-base font-semibold text-gray-900">{{ __('referrals.share.social_title') }}</h2>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <a
                        href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('referrals.apply', $referralCode->code)) }}&quote={{ urlencode($shareText) }}"
                        target="_blank"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >Facebook</a>

                    <a
                        href="https://twitter.com/intent/tweet?text={{ urlencode($shareText) }}&url={{ urlencode(route('referrals.apply', $referralCode->code)) }}"
                        target="_blank"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >Twitter</a>

                    <a
                        href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('referrals.apply', $referralCode->code)) }}"
                        target="_blank"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >LinkedIn</a>

                    <a
                        href="https://wa.me/?text={{ urlencode($shareText) }}"
                        target="_blank"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >WhatsApp</a>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 p-6">
                <h2 class="text-base font-semibold text-gray-900">{{ __('referrals.share.email_title') }}</h2>
                <div class="mt-4 space-y-3">
                    <div>
                        <label for="email_subject" class="mb-1 block text-sm font-medium text-gray-700">{{ __('referrals.share.email_subject_label') }}</label>
                        <input id="email_subject" type="text" value="{{ __('referrals.share.email_subject') }}" readonly class="block w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label for="email_body" class="mb-1 block text-sm font-medium text-gray-700">{{ __('referrals.share.email_message_label') }}</label>
                        <textarea id="email_body" rows="4" readonly class="block w-full rounded-md border-gray-300 text-sm">{{ $shareText }}</textarea>
                    </div>
                    <button type="button" onclick="copyEmailContent()" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ __('referrals.share.copy_email') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900">{{ __('referrals.share.link_title') }}</h2>
            <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                <input id="referral_link" type="text" value="{{ route('referrals.apply', $referralCode->code) }}" readonly class="block w-full rounded-md border-gray-300 text-sm">
                <button type="button" onclick="copyLink()" class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    {{ __('referrals.share.copy_link') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        function notify(message) {
            const notification = document.createElement('div');
            notification.className = 'fixed bottom-4 right-4 z-50 rounded-md bg-gray-900 px-4 py-2 text-sm text-white shadow';
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 2200);
        }

        function copyCode() {
            navigator.clipboard.writeText(@json($referralCode->code)).then(() => {
                notify(@json(__('referrals.share.notifications.code_copied')));
            });
        }

        function copyLink() {
            const link = document.getElementById('referral_link').value;
            navigator.clipboard.writeText(link).then(() => {
                notify(@json(__('referrals.share.notifications.link_copied')));
            });
        }

        function copyEmailContent() {
            const subject = document.getElementById('email_subject').value;
            const body = document.getElementById('email_body').value;
            navigator.clipboard.writeText(`Subject: ${subject}\n\n${body}`).then(() => {
                notify(@json(__('referrals.share.notifications.email_copied')));
            });
        }
    </script>
</x-layouts.templates.account>
