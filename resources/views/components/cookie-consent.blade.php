@php
    $cookieName = config('privacy.consent.cookie_name', 'statybae_cookie_consent');
    $lifetimeDays = (int) config('privacy.consent.cookie_lifetime_days', 365);
    $privacyRoute = \Illuminate\Support\Facades\Route::has('localized.legal.privacy')
        ? route('localized.legal.privacy')
        : url('/legal/privacy');
    $cookieRoute = \Illuminate\Support\Facades\Route::has('localized.legal.cookies')
        ? route('localized.legal.cookies')
        : url('/legal/cookies');
@endphp

<div id="cookie-consent-banner"
     class="fixed inset-x-0 bottom-4 z-[9999] mx-auto max-w-5xl rounded-lg border border-sage/30 bg-dark shadow-2xl shadow-black/40 px-6 py-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
     role="dialog"
     aria-live="polite"
     aria-label="{{ __('translations.cookie_prompt_label') }}"
     hidden>
    <div class="space-y-2 text-sm text-white">
        <p class="font-semibold">
            {{ __('translations.cookie_prompt_title') }}
        </p>
        <p class="text-sage/80">
            {{ __('translations.cookie_prompt_description') }}
            <a href="{{ $privacyRoute }}" class="font-medium text-sage hover:text-white underline transition-colors duration-200">
                {{ __('ui.privacy_policy') }}
            </a>
            <span class="text-sage/50"> · </span>
            <a href="{{ $cookieRoute }}" class="font-medium text-sage hover:text-white underline transition-colors duration-200">
                {{ __('ui.cookie_policy') }}
            </a>
        </p>
    </div>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <button type="button"
                data-cookie-action="reject"
                class="inline-flex items-center justify-center rounded-lg border border-sage/30 px-4 py-2 text-sm font-medium text-sage hover:bg-sage/20 hover:text-white focus:outline-none focus:ring-2 focus:ring-sage/40 focus:ring-offset-2 focus:ring-offset-dark transition-all duration-200">
            {{ __('translations.cookie_prompt_reject') }}
        </button>
        <button type="button"
                data-cookie-action="accept"
                class="inline-flex items-center justify-center rounded-lg bg-sage px-4 py-2 text-sm font-medium text-dark shadow hover:bg-sage/90 focus:outline-none focus:ring-2 focus:ring-sage/50 focus:ring-offset-2 focus:ring-offset-dark transition-all duration-200">
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
