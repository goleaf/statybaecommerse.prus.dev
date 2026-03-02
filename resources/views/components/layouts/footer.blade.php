@php
    $hasSettings = \Illuminate\Support\Facades\Schema::hasTable('settings');
    $companyName = $hasSettings ? app_setting('company_name') ?? config('app.name') : config('app.name');
    $companyEmail = $hasSettings ? app_setting('email') ?? null : null;
    $companyPhone = $hasSettings ? app_setting('phone_number') ?? null : null;
    $companyAddress = $hasSettings ? app_setting('company_address') ?? null : null;
    $socialFacebook = $hasSettings ? app_setting('social_facebook') ?? '#' : '#';
    $socialInstagram = $hasSettings ? app_setting('social_instagram') ?? '#' : '#';
    $blockedGithubFooterUrl = 'github.com/prus-dev/statybaecommerse.prus.dev';

    if (str_contains(strtolower((string) $socialFacebook), $blockedGithubFooterUrl)) {
        $socialFacebook = null;
    }

    if (str_contains(strtolower((string) $socialInstagram), $blockedGithubFooterUrl)) {
        $socialInstagram = null;
    }

    $locale = app()->getLocale();
    $homeUrl = \Illuminate\Support\Facades\Route::has('localized.home')
        ? route('localized.home', ['locale' => $locale])
        : (\Illuminate\Support\Facades\Route::has('home')
            ? route('home')
            : url('/'));
    $aboutUrl = \Illuminate\Support\Facades\Route::has('localized.about')
        ? route('localized.about', ['locale' => $locale])
        : (\Illuminate\Support\Facades\Route::has('frontend.about.index') ? route('frontend.about.index') : url('/about'));
    $contactUrl = \Illuminate\Support\Facades\Route::has('frontend.contact.index') ? route('frontend.contact.index') : url('/contact');
    $securePaymentUrl = \Illuminate\Support\Facades\Route::has('frontend.legal.terms') ? route('frontend.legal.terms') : url('/legal/terms');
    $shippingUrl = \Illuminate\Support\Facades\Route::has('frontend.legal.shipping')
        ? route('frontend.legal.shipping')
        : (\Illuminate\Support\Facades\Route::has('legal.show') ? route('legal.show', 'shipping') : url('/legal/shipping'));
    $returnsUrl = \Illuminate\Support\Facades\Route::has('frontend.legal.returns')
        ? route('frontend.legal.returns')
        : (\Illuminate\Support\Facades\Route::has('legal.show') ? route('legal.show', 'refund') : url('/legal/returns'));
@endphp

<footer aria-labelledby="footer-heading" class="bg-dark text-sage relative">
    <h2 id="footer-heading" class="sr-only">{{ __('messages.footer_heading') }}</h2>

    <!-- Geometric top separator with centered diamond -->
    <div class="w-full h-[1px] bg-brand-primary relative">
        <div class="aspect-square h-6 bg-brand-primary absolute -top-3 left-1/2 -translate-x-1/2 rotate-45"></div>
    </div>

    <!-- Main Footer Content -->
    <div class="max-w-site mx-auto px-4 sm:px-6 lg:px-8 relative pt-16 pb-12">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-12 md:gap-16">
            <!-- Left: Company Info -->
            <div class="md:col-span-4 text-center md:text-left">
                <div class="mb-6 text-center md:text-left">
                    <a href="{{ $homeUrl }}" class="group" aria-label="{{ __('messages.frontend') }}">
                        <img src="{{ asset('images/logo-white.png') }}" alt="{{ config('app.name') }}"
                            class="h-16 w-auto mx-auto md:mx-0 object-contain">
                    </a>
                </div>

                <div class="text-ash/80 mb-8 leading-relaxed">
                    {{ __('messages.footer_tagline') }}
                </div>
            </div>

            <!-- Center: Navigation - 2 Sections -->
            <nav aria-label="{{ __('messages.footer_heading') }}"
                class="md:col-span-4 md:border-l md:border-ash/20 md:pl-8 text-left">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                    <!-- Section 1: About -->
                    <div>
                        <p class="text-xs uppercase tracking-wide text-ash/70 flex items-center gap-2 mb-4">
                            <span class="w-8 h-[2px] bg-brand-primary"></span>
                            // {{ __('messages.footer_about') }}
                        </p>
                        <div class="space-y-3 footer-nav">
                            <div>
                                <x-footer-link href="{{ $aboutUrl }}"
                                    class="text-ash hover:text-sage transition-colors duration-200 footer-nav-link">
                                    <svg class="text-ash" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                                    </svg>
                                    <span>{{ __('messages.footer_about') }}</span>
                                </x-footer-link>
                            </div>
                            <div>
                                <x-footer-link href="{{ $securePaymentUrl }}"
                                    class="text-ash hover:text-sage transition-colors duration-200 footer-nav-link">
                                    <svg class="text-ash" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                                    </svg>
                                    <span>{{ __('messages.footer_secure_payment') }}</span>
                                </x-footer-link>
                            </div>
                            <div>
                                <x-footer-link href="{{ $contactUrl }}"
                                    class="text-ash hover:text-sage transition-colors duration-200 footer-nav-link">
                                    <svg class="text-ash" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                                    </svg>
                                    <span>{{ __('messages.footer_contact') }}</span>
                                </x-footer-link>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Shop/Legal -->
                    <div>
                        <p class="text-xs uppercase tracking-wide text-ash/70 flex items-center gap-2 mb-4">
                            <span class="w-8 h-[2px] bg-brand-primary"></span>
                            // {{ __('messages.footer_shop') }}
                        </p>
                        <div class="space-y-3 footer-nav">
                            <div>
                                <x-footer-link href="{{ $shippingUrl }}"
                                    class="text-ash hover:text-sage transition-colors duration-200 footer-nav-link">
                                    <svg class="text-ash" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                                    </svg>
                                    <span>{{ __('messages.legal_shipping') }}</span>
                                </x-footer-link>
                            </div>
                            <div>
                                <x-footer-link href="{{ $returnsUrl }}"
                                    class="text-ash hover:text-sage transition-colors duration-200 footer-nav-link">
                                    <svg class="text-ash" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                                    </svg>
                                    <span>{{ __('messages.footer_returns_refunds') }}</span>
                                </x-footer-link>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Right: Newsletter & Contacts -->
            <div class="md:col-span-4 md:border-l md:border-ash/20 md:pl-6 text-left">
                <p class="text-xs uppercase tracking-wide text-ash/70 flex items-center gap-2 mb-4">
                    <span class="w-8 h-[2px] bg-brand-primary"></span>
                    // {{ __('messages.footer_subscribe_title') }}
                </p>
                <div class="space-y-8">
                    <div>
                        <p class="text-sm leading-relaxed text-ash/80 mb-6">
                            {{ __('messages.footer_subscribe_desc') }}
                        </p>
                        {{-- Newsletter Form --}}
                        <livewire:newsletter-subscription />
                    </div>
                    <div class="flex items-center space-x-6">
                        @if ($socialFacebook)
                            <a href="{{ $socialFacebook }}"
                                class="text-ash hover:text-sage transition-colors duration-200 transform hover:scale-110 p-2 rounded-full">
                                <span class="sr-only">{{ __('messages.footer_facebook') }}</span>
                                <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>
                        @endif
                        @if ($socialInstagram)
                            <a href="{{ $socialInstagram }}"
                                class="text-ash hover:text-sage transition-colors duration-200 transform hover:scale-110 p-2 rounded-full">
                                <span class="sr-only">{{ __('messages.footer_instagram') }}</span>
                                <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact & Hours Section -->
        <div class="pb-16 lg:flex lg:items-center lg:justify-between lg:pb-24 mt-12 pt-12 border-t border-ash/20">
            <div class="space-y-8 lg:space-y-0 lg:flex lg:flex-1 lg:gap-12">
                <div>
                    <dl class="text-xs uppercase tracking-wide text-ash/70 flex items-center gap-2 mb-4">
                        <span class="w-8 h-[2px] bg-brand-primary"></span>
                        <span>{{ __('messages.footer_contact') }}</span>
                    </dl>
                    <ul class="flex flex-col space-y-3 text-sm text-ash">
                        @if ($companyPhone)
                            <li class="flex items-center gap-3">
                                <svg class="size-5 text-sage" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2 5a2 2 0 012-2h2l2 5-2 1a14 14 0 006 6l1-2 5 2v2a2 2 0 01-2 2h-1C9.163 19 5 14.837 5 9V8a2 2 0 00-2-2z" />
                                </svg>
                                <a href="tel:{{ $companyPhone }}"
                                    class="hover:text-sage transition-colors duration-200 font-medium">
                                    {{ $companyPhone }}
                                </a>
                            </li>
                        @endif

                        @if ($companyEmail)
                            <li class="flex items-center gap-3">
                                <svg class="size-5 text-sage" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 8l9 6 9-6M4 6h16a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z" />
                                </svg>
                                <a href="mailto:{{ $companyEmail }}"
                                    class="hover:text-sage transition-colors duration-200 font-medium">
                                    {{ $companyEmail }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
                <div>
                    <dl class="text-xs uppercase tracking-wide text-ash/70 flex items-center gap-2 mb-4">
                        <span class="w-8 h-[2px] bg-brand-primary"></span>
                        <span>{{ __('messages.footer_hours') }}</span>
                    </dl>
                    <dt class="text-sm leading-relaxed text-ash">
                        {{ __('messages.footer_hours_desc') }}
                    </dt>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Footer -->
    <div class="border-t border-ash/30">
        <div class="max-w-site mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col items-center border-t-0 sm:flex-row sm:justify-between">
                <p class="text-sm text-ash"></p>
                <div class="mt-8 flex items-center gap-6 divide-x divide-ash/30 sm:mt-0">
                    @if (auth()->check() && auth()->user()->can('view orders'))
                        <x-link href="{{ route('exports.index') }}"
                            class="inline-flex px-3 text-sm leading-5 text-ash hover:text-sage transition-colors duration-200 font-medium">
                            {{ __('messages.exports') }}
                        </x-link>
                    @endif
                    @php
                        $hasLegals = \Illuminate\Support\Facades\Schema::hasTable('legals');
                    @endphp
                    @if ($hasLegals)
                        @php
                            $legalModel = app(\App\Models\Legal::class);
                            $privacy = $legalModel->newQuery()->where('key', 'privacy')->where('is_enabled', true)->first();
                            $terms = $legalModel->newQuery()->where('key', 'terms')->where('is_enabled', true)->first();
                            $refund = $legalModel->newQuery()->where('key', 'refund')->where('is_enabled', true)->first();
                            $shipping = $legalModel
                                ->newQuery()
                                ->where('key', 'shipping')
                                ->where('is_enabled', true)
                                ->first();
                        @endphp
                        @if ($privacy)
                            <x-link
                                href="{{ Route::has('localized.legal.privacy') ? route('localized.legal.privacy', ['locale' => $locale]) : url('/legal/privacy') }}"
                                class="inline-flex px-3 text-sm leading-5 text-ash hover:text-sage transition-colors duration-200 font-medium">
                                {{ __('messages.legal_privacy') }}
                            </x-link>
                        @endif
                        @if ($terms)
                            <x-link
                                href="{{ Route::has('localized.legal.terms') ? route('localized.legal.terms', ['locale' => $locale]) : url('/legal/terms') }}"
                                class="inline-flex px-3 text-sm leading-5 text-ash hover:text-sage transition-colors duration-200 font-medium">
                                {{ __('messages.legal_terms') }}
                            </x-link>
                        @endif
                        @if ($refund)
                            <x-link
                                href="{{ Route::has('localized.legal.returns') ? route('localized.legal.returns', ['locale' => $locale]) : url('/legal/returns') }}"
                                class="inline-flex px-3 text-sm leading-5 text-ash hover:text-sage transition-colors duration-200 font-medium">
                                {{ __('messages.legal_refund') }}
                            </x-link>
                        @endif
                        @if ($shipping)
                            <x-link
                                href="{{ Route::has('localized.legal.shipping') ? route('localized.legal.shipping', ['locale' => $locale]) : url('/legal/shipping') }}"
                                class="inline-flex px-3 text-sm leading-5 text-ash hover:text-sage transition-colors duration-200 font-medium">
                                {{ __('messages.legal_shipping') }}
                            </x-link>
                        @endif
                    @else
                        <x-link href="{{ route('frontend.legal.privacy') }}"
                            class="inline-flex px-3 text-sm leading-5 text-ash hover:text-sage transition-colors duration-200 font-medium">
                            {{ __('messages.legal_privacy') }}
                        </x-link>
                        <x-link href="{{ route('frontend.legal.cookies') }}"
                            class="inline-flex px-3 text-sm leading-5 text-ash hover:text-sage transition-colors duration-200 font-medium">
                            {{ __('messages.frontend_legal_cookie_policy') }}
                        </x-link>
                        <x-link href="{{ route('frontend.legal.terms') }}"
                            class="inline-flex px-3 text-sm leading-5 text-ash hover:text-sage transition-colors duration-200 font-medium">
                            {{ __('messages.legal_terms') }}
                        </x-link>
                    @endif
                </div>
            </div>
        </div>
    </div>
</footer>
