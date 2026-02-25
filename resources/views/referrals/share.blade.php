<x-layouts.templates.account title="{{ __('messages.referrals') }}">
    <div class="space-y-6">
        <!-- Header -->
        <header class="border-b border-gray-200 dark:border-white/10 pb-5 mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('referrals.index') }}" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-300 transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Share Referral Code</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Share your unique code to earn rewards.</p>
                </div>
            </div>
        </header>

        <!-- Referral Code Card -->
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-6 border border-gray-100 dark:border-white/5 shadow-sm text-center">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Your Unique Code</h3>
            
            <div class="inline-flex items-center px-6 py-3 bg-primary-100 dark:bg-primary-900/30 rounded-lg mb-6 border border-primary-200 dark:border-primary-800/30">
                <span class="text-2xl font-mono font-bold text-primary-700 dark:text-primary-300 tracking-wider">
                    {{ $user->referral_code }}
                </span>
            </div>

            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                {{ __('messages.referrals', 'Share this code with your friends and earn rewards when they make their first purchase.') }}
            </p>

            <button onclick="copyCode()" class="inline-flex justify-center items-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                Copy Code
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Share Options -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-white/10 shadow-sm">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
                    </svg>
                    Share via Social Media
                </h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('referrals.apply', $user->referral_code)) }}&quote={{ urlencode($shareText) }}" 
                       target="_blank"
                       class="inline-flex items-center justify-center px-4 py-2 bg-[#1877F2] text-white rounded-md hover:bg-[#1864D9] transition-colors text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        Facebook
                    </a>
                    
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($shareText) }}&url={{ urlencode(route('referrals.apply', $user->referral_code)) }}" 
                       target="_blank"
                       class="inline-flex items-center justify-center px-4 py-2 bg-[#1DA1F2] text-white rounded-md hover:bg-[#1A91DA] transition-colors text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                        Twitter
                    </a>

                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('referrals.apply', $user->referral_code)) }}" 
                       target="_blank"
                       class="inline-flex items-center justify-center px-4 py-2 bg-[#0A66C2] text-white rounded-md hover:bg-[#0958A6] transition-colors text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        LinkedIn
                    </a>

                    <a href="https://wa.me/?text={{ urlencode($shareText) }}" 
                       target="_blank"
                       class="inline-flex items-center justify-center px-4 py-2 bg-[#25D366] text-white rounded-md hover:bg-[#20B058] transition-colors text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/></svg>
                        WhatsApp
                    </a>
                </div>
            </div>

            <!-- Email Share -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-white/10 shadow-sm">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    Send Email
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="email_subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject</label>
                        <input type="text" id="email_subject" value="Here's a referral link!" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                    </div>
                    <div>
                        <label for="email_body" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message</label>
                        <textarea id="email_body" rows="4" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>{{ $shareText }}</textarea>
                    </div>
                    <button onclick="copyEmailContent()" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        Copy Email Content
                    </button>
                </div>
            </div>
        </div>

        <!-- Share Link -->
        <div class="bg-primary-50 dark:bg-primary-900/10 rounded-xl p-6 border border-primary-100 dark:border-primary-900/30">
            <h3 class="text-lg font-medium text-primary-900 dark:text-primary-100 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                </svg>
                Your Personal Referral Link
            </h3>
            <div class="flex items-center gap-3">
                <input type="text" id="referral_link" value="{{ route('referrals.apply', $user->referral_code) }}" readonly class="block w-full rounded-md border-primary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-primary-900/50 dark:border-primary-800 dark:text-primary-200">
                <button onclick="copyLink()" class="shrink-0 inline-flex items-center px-4 py-2 border border-primary-300 dark:border-primary-700 shadow-sm text-sm font-medium rounded-md text-primary-700 dark:text-primary-300 bg-white dark:bg-gray-800 hover:bg-primary-50 dark:hover:bg-primary-900/50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    Copy Link
                </button>
            </div>
        </div>
    </div>

    <!-- Notifications Script -->
    <script>
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed bottom-4 right-4 z-50 rounded-lg px-4 py-3 shadow-lg flex items-center gap-3 transform transition-all duration-300 translate-y-10 opacity-0 ${
            type === 'success' ? 'bg-gray-900 text-white' : 'bg-red-600 text-white'
        }`;
        
        notification.innerHTML = `
            <svg class="h-5 w-5 ${type === 'success' ? 'text-green-400' : 'text-white'}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                ${type === 'success' 
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'}
            </svg>
            <span class="text-sm font-medium">${message}</span>
        `;
        
        document.body.appendChild(notification);
        
        // Trigger enter animation
        setTimeout(() => {
            notification.classList.remove('translate-y-10', 'opacity-0');
        }, 10);
        
        setTimeout(() => {
            notification.classList.add('translate-y-10', 'opacity-0');
            setTimeout(() => document.body.removeChild(notification), 300);
        }, 3000);
    }

    function copyCode() {
        const code = '{{ $user->referral_code }}';
        navigator.clipboard.writeText(code).then(() => {
            showNotification('Code copied to clipboard!', 'success');
        });
    }

    function copyLink() {
        const link = document.getElementById('referral_link').value;
        navigator.clipboard.writeText(link).then(() => {
            showNotification('Link copied to clipboard!', 'success');
        });
    }

    function copyEmailContent() {
        const subject = document.getElementById('email_subject').value;
        const body = document.getElementById('email_body').value;
        navigator.clipboard.writeText(`Subject: ${subject}\n\n${body}`).then(() => {
            showNotification('Email content copied!', 'success');
        });
    }
    </script>
</x-layouts.templates.account>
