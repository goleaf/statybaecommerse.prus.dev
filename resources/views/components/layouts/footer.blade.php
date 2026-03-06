@php
    $hasSettings = \Illuminate\Support\Facades\Schema::hasTable('settings');
    $companyName = $hasSettings ? app_setting('company_name') ?? config('app.name') : config('app.name');
    $companyEmail = $hasSettings ? app_setting('email') ?? null : null;
    $companyPhone = $hasSettings ? app_setting('phone_number') ?? null : null;
    $companyAddress = $hasSettings ? app_setting('company_address') ?? null : null;
    $socialFacebook = 'https://www.facebook.com/p/Egis-statyba-100076148592033/';
    $blockedGithubFooterUrl = 'github.com/prus-dev/statybaecommerse.prus.dev';

    if (str_contains(strtolower((string) $socialFacebook), $blockedGithubFooterUrl)) {
        $socialFacebook = null;
    }

    $homeUrl = \Illuminate\Support\Facades\Route::has('home')
        ? route('home', [])
        : (\Illuminate\Support\Facades\Route::has('home')
            ? route('home')
            : url('/'));

    $resolveFooterUrl = static function (array $routeNames, ?string $fallback = null, array $parameters = []): ?string {
        foreach ($routeNames as $routeName) {
            if (\Illuminate\Support\Facades\Route::has($routeName)) {
                return route($routeName, $parameters);
            }
        }

        return $fallback;
    };

    $faqUrl = $resolveFooterUrl(['localized.info.faq', 'frontend.info.faq', 'frontend.faq.index', 'faq.index']);
    $shippingUrl = $resolveFooterUrl(['localized.legal.shipping']);
    $paymentMethodsUrl = $resolveFooterUrl(['localized.info.payment-methods', 'frontend.info.payment-methods', 'frontend.payment-methods.index', 'payment-methods.index', 'localized.legal.payment']);
    $returnsUrl = $resolveFooterUrl(['localized.legal.returns']);
    $termsUrl = $resolveFooterUrl(['localized.legal.terms']);
    $privacyUrl = $resolveFooterUrl(['localized.legal.privacy']);
    $popularProductsUrl = $resolveFooterUrl(['localized.info.popular-products', 'frontend.info.popular-products', 'frontend.products.popular']);
    $buildingMaterialsUrl = $resolveFooterUrl(['localized.info.building-materials', 'frontend.info.building-materials']);
    if (! $buildingMaterialsUrl) {
        $buildingMaterialsUrl = $resolveFooterUrl(['frontend.categories.show'], null, ['category' => 'statybos-medziagos']);
    }

    $toolsEquipmentUrl = $resolveFooterUrl(['localized.info.tools-equipment', 'frontend.info.tools-equipment']);
    if (! $toolsEquipmentUrl) {
        $toolsEquipmentUrl = $resolveFooterUrl(['frontend.categories.show'], null, ['category' => 'irankiai-iranga']);
    }
    $specialOffersUrl = $resolveFooterUrl(['localized.info.special-offers', 'frontend.info.special-offers', 'frontend.discounts.index']);
    $servicesForCraftsmenUrl = $resolveFooterUrl(['localized.info.services-for-craftsmen', 'frontend.info.services-for-craftsmen', 'frontend.services.index', 'services.index']);

    $footerNavigationSections = [
        [
            'title' => __('messages.footer_help_information'),
            'links' => [
                ['label' => __('messages.footer_faq_long'), 'url' => $faqUrl],
                ['label' => __('messages.footer_shipping_pickup'), 'url' => $shippingUrl],
                ['label' => __('messages.footer_payment_methods'), 'url' => $paymentMethodsUrl],
                ['label' => __('messages.footer_returns_warranty'), 'url' => $returnsUrl],
                ['label' => __('messages.footer_sales_rules'), 'url' => $termsUrl],
                ['label' => __('messages.footer_privacy_policy'), 'url' => $privacyUrl],
            ],
        ],
        [
            'title' => __('messages.footer_catalog_services'),
            'links' => [
                ['label' => __('messages.footer_popular_products'), 'url' => $popularProductsUrl],
                ['label' => __('messages.footer_building_materials'), 'url' => $buildingMaterialsUrl],
                ['label' => __('messages.footer_tools_equipment'), 'url' => $toolsEquipmentUrl],
                ['label' => __('messages.footer_special_offers'), 'url' => $specialOffersUrl],
                ['label' => __('messages.footer_services_for_craftsmen'), 'url' => $servicesForCraftsmenUrl],
            ],
        ],
    ];

    $footerLinkClass = 'text-ash hover:text-sage transition-colors duration-200 footer-nav-link';
    $footerUnavailableLinkClass = 'text-sm font-medium text-[#6b6a68] footer-nav-link cursor-default select-none';
    $footerLogoUrl = asset('images/logo.png');

    if (file_exists(public_path('images/logo-white.png'))) {
        $footerLogoUrl = asset('images/logo-white.png');
    } elseif (\App\Support\ViteManifest::isPopulated()) {
        try {
            $footerLogoUrl = \Illuminate\Support\Facades\Vite::asset('resources/images/logo-white.png');
        } catch (\Throwable $exception) {
            // Keep fallback logo URL when the Vite manifest does not include this asset.
        }
    }
@endphp

<footer aria-labelledby="footer-heading" class="bg-dark text-sage relative">
    <h2 id="footer-heading" class="sr-only">{{ __('messages.footer_heading') }}</h2>

    <!-- Geometric top separator with centered diamond -->
    <div class="w-full h-[1px] bg-brand-primary relative">
        <div class="aspect-square h-6 bg-brand-primary absolute -top-3 left-1/2 -translate-x-1/2 rotate-45"></div>
    </div>

    <!-- Main Footer Content -->
    <div class="max-w-site mx-auto w-full px-4 sm:px-6 lg:px-8 relative pt-16 pb-12">
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-12 lg:gap-10">
            <div class="space-y-10 text-center lg:col-span-4 lg:text-left">
                <div class="space-y-6">
                    <a href="{{ $homeUrl }}" class="group inline-flex" aria-label="{{ __('messages.frontend') }}">
                        <img src="{{ $footerLogoUrl }}" alt="{{ config('app.name') }}"
                            class="h-16 w-auto mx-auto lg:mx-0 object-contain">
                    </a>

                    <h3 class="text-2xl font-semibold text-sage">Statybos E-commerce</h3>

                    <div class="text-sm leading-7 text-ash/80">
                        {{ __('messages.footer_tagline') }}
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-1">
                    <div class="text-left">
                        <p class="text-xs uppercase tracking-wide text-ash/70 flex items-center gap-2 mb-4">
                            <span class="w-8 h-[2px] bg-brand-primary"></span>
                            <span>{{ __('messages.footer_contact') }}</span>
                        </p>
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
                                        class="hover:text-sage transition-colors duration-200 font-medium break-all">
                                        {{ $companyEmail }}
                                    </a>
                                </li>
                            @endif

                            @if ($companyAddress)
                                <li class="flex items-start gap-3">
                                    <svg class="mt-0.5 size-5 text-sage" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11z" />
                                        <circle cx="12" cy="10" r="2.5" />
                                    </svg>
                                    <span class="leading-6">{{ $companyAddress }}</span>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <div class="text-left">
                        <p class="text-xs uppercase tracking-wide text-ash/70 flex items-center gap-2 mb-4">
                            <span class="w-8 h-[2px] bg-brand-primary"></span>
                            <span>{{ __('messages.footer_hours') }}</span>
                        </p>
                        <p class="text-sm leading-7 text-ash">
                            {{ __('messages.footer_hours_desc') }}
                        </p>
                    </div>
                </div>
            </div>

            <nav aria-label="{{ __('messages.footer_heading') }}"
                class="text-left lg:col-span-4 lg:border-l lg:border-ash/20 lg:pl-8">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                    @foreach ($footerNavigationSections as $section)
                        <div>
                            <p class="text-xs uppercase tracking-wide text-ash/70 flex items-center gap-2 mb-4">
                                <span class="w-8 h-[2px] bg-brand-primary"></span>
                                {{ $section['title'] }}
                            </p>
                            <div class="space-y-3 footer-nav">
                                @foreach ($section['links'] as $link)
                                    <div>
                                        @if ($link['url'])
                                            <x-footer-link href="{{ $link['url'] }}" class="{{ $footerLinkClass }}">
                                                <svg class="text-current" fill="currentColor" viewBox="0 0 24 24"
                                                    aria-hidden="true">
                                                    <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                                                </svg>
                                                <span>{{ $link['label'] }}</span>
                                            </x-footer-link>
                                        @else
                                            <span class="{{ $footerUnavailableLinkClass }}" aria-disabled="true">
                                                <svg class="text-current" fill="currentColor" viewBox="0 0 24 24"
                                                    aria-hidden="true">
                                                    <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                                                </svg>
                                                <span>{{ $link['label'] }}</span>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </nav>

            <div class="text-left lg:col-span-4 lg:border-l lg:border-ash/20 lg:pl-8">
                <p class="text-xs uppercase tracking-wide text-ash/70 flex items-center gap-2 mb-4">
                    <span class="w-8 h-[2px] bg-brand-primary"></span>
                    {{ __('messages.footer_subscribe_title') }}
                </p>
                <div class="space-y-8">
                    <div>
                        <p class="text-sm leading-relaxed text-ash/80 mb-6">
                            {{ __('messages.footer_subscribe_desc') }}
                        </p>
                        {{-- Newsletter Form --}}
                        <livewire:newsletter-subscription />
                    </div>
                    <div class="flex flex-wrap items-center gap-4">
                        @if ($socialFacebook)
                            <a href="{{ $socialFacebook }}"
                                class="text-ash hover:text-sage transition-colors duration-200 transform hover:scale-110 p-2 rounded-full border border-ash/15 bg-black/10">
                                <span class="sr-only">{{ __('messages.footer_facebook') }}</span>
                                <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Footer -->
    <div class="border-t border-ash/30">
        <div class="max-w-site mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
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
                                href="{{ Route::has('localized.legal.privacy') ? route('localized.legal.privacy', []) : url('/legal/privacy') }}"
                                class="inline-flex px-3 text-sm leading-5 text-ash hover:text-sage transition-colors duration-200 font-medium">
                                {{ __('messages.legal_privacy') }}
                            </x-link>
                        @endif
                        @if ($terms)
                            <x-link
                                href="{{ Route::has('localized.legal.terms') ? route('localized.legal.terms', []) : url('/legal/terms') }}"
                                class="inline-flex px-3 text-sm leading-5 text-ash hover:text-sage transition-colors duration-200 font-medium">
                                {{ __('messages.legal_terms') }}
                            </x-link>
                        @endif
                        @if ($refund)
                            <x-link
                                href="{{ Route::has('localized.legal.returns') ? route('localized.legal.returns', []) : url('/legal/returns') }}"
                                class="inline-flex px-3 text-sm leading-5 text-ash hover:text-sage transition-colors duration-200 font-medium">
                                {{ __('messages.legal_refund') }}
                            </x-link>
                        @endif
                        @if ($shipping)
                            <x-link
                                href="{{ Route::has('localized.legal.shipping') ? route('localized.legal.shipping', []) : url('/legal/shipping') }}"
                                class="inline-flex px-3 text-sm leading-5 text-ash hover:text-sage transition-colors duration-200 font-medium">
                                {{ __('messages.legal_shipping') }}
                            </x-link>
                        @endif
                    @else
                        <x-link href="{{ Route::has('localized.legal.privacy') ? route('localized.legal.privacy', []) : url('/legal/privacy') }}"
                            class="inline-flex px-3 text-sm leading-5 text-ash hover:text-sage transition-colors duration-200 font-medium">
                            {{ __('messages.legal_privacy') }}
                        </x-link>
                        <x-link href="{{ Route::has('localized.legal.cookies') ? route('localized.legal.cookies', []) : url('/legal/cookies') }}"
                            class="inline-flex px-3 text-sm leading-5 text-ash hover:text-sage transition-colors duration-200 font-medium">
                            {{ __('messages.frontend_legal_cookie_policy') }}
                        </x-link>
                        <x-link href="{{ Route::has('localized.legal.terms') ? route('localized.legal.terms', []) : url('/legal/terms') }}"
                            class="inline-flex px-3 text-sm leading-5 text-ash hover:text-sage transition-colors duration-200 font-medium">
                            {{ __('messages.legal_terms') }}
                        </x-link>
                    @endif
                </div>
            </div>
        </div>
    </div>
</footer>


