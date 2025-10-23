@php
    $cookieName = config('privacy.consent.cookie_name', 'statybae_cookie_consent');
    $lifetimeDays = (int) config('privacy.consent.cookie_lifetime_days', 365);
    $privacyRoute = \Illuminate\Support\Facades\Route::has('frontend.legal.privacy') ? route('frontend.legal.privacy') : url('/legal/privacy');
    $cookieRoute = \Illuminate\Support\Facades\Route::has('frontend.legal.cookies') ? route('frontend.legal.cookies') : url('/legal/cookies');
@endphp

<div id="cookie-consent-banner"
     class="fixed inset-x-0 bottom-4 z-40 mx-auto max-w-5xl rounded-2xl border border-slate-200 bg-white/95 shadow-xl shadow-slate-300/40 backdrop-blur px-6 py-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
     role="dialog"
     aria-live="polite"
     aria-label="{{ __('translations.cookie_prompt_label') }}"
     hidden>
    <div class="space-y-2 text-sm text-slate-700">
        <p class="font-semibold text-slate-900">
            {{ __('translations.cookie_prompt_title') }}
        </p>
        <p>
            {{ __('translations.cookie_prompt_description') }}
            <a href="{{ $privacyRoute }}" class="font-medium text-blue-600 hover:text-blue-700 underline">
                {{ __('Privacy Policy') }}
            </a>
            ·
            <a href="{{ $cookieRoute }}" class="font-medium text-blue-600 hover:text-blue-700 underline">
                {{ __('Cookie Policy') }}
            </a>
        </p>
    </div>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <button type="button"
                data-cookie-action="reject"
                class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            {{ __('translations.cookie_prompt_reject') }}
        </button>
        <button type="button"
                data-cookie-action="accept"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            {{ __('translations.cookie_prompt_accept') }}
        </button>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const banner = document.getElementById('cookie-consent-banner');
            if (!banner) {
                return;
            }

            const storageKey = @json($cookieName);
            const lifetimeDays = Number(@json($lifetimeDays));
            const lifetimeMs = lifetimeDays > 0 ? lifetimeDays * 24 * 60 * 60 * 1000 : null;

            function setCookie(value) {
                if (!lifetimeMs) {
                    return;
                }

                const expires = new Date(Date.now() + lifetimeMs);
                document.cookie = `${storageKey}=${value}; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;
            }

            function storeConsent(status) {
                const payload = {
                    status,
                    updated_at: new Date().toISOString(),
                };
                try {
                    window.localStorage.setItem(storageKey, JSON.stringify(payload));
                } catch (error) {
                    // Ignore storage errors (private mode, etc.).
                }
                setCookie(status);
            }

            function hasValidConsent() {
                try {
                    const raw = window.localStorage.getItem(storageKey);
                    if (!raw) {
                        return false;
                    }
                    const parsed = JSON.parse(raw);
                    if (!parsed || typeof parsed.updated_at !== 'string') {
                        return false;
                    }
                    if (!lifetimeMs) {
                        return true;
                    }
                    const updatedAt = Date.parse(parsed.updated_at);
                    if (Number.isNaN(updatedAt)) {
                        return false;
                    }
                    return Date.now() - updatedAt < lifetimeMs;
                } catch (error) {
                    return false;
                }
            }

            function hideBanner() {
                banner.setAttribute('hidden', 'hidden');
            }

            function showBanner() {
                banner.removeAttribute('hidden');
            }

            banner.querySelectorAll('[data-cookie-action]').forEach((button) => {
                button.addEventListener('click', (event) => {
                    const action = event.currentTarget.getAttribute('data-cookie-action');
                    if (action === 'accept') {
                        storeConsent('accepted');
                    } else {
                        storeConsent('rejected');
                    }
                    hideBanner();
                });
            });

            if (!hasValidConsent()) {
                showBanner();
            }
        })();
    </script>
@endpush
