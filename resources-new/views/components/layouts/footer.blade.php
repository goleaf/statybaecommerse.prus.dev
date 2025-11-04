@php
$hasSettings = \Illuminate\Support\Facades\Schema::hasTable('settings');
$companyName = $hasSettings ? app_setting('company_name') ?? config('app.name') : config('app.name');
$companyEmail = $hasSettings ? app_setting('email') ?? null : null;
$companyPhone = $hasSettings ? app_setting('phone_number') ?? null : null;
$companyAddress = $hasSettings ? app_setting('company_address') ?? null : null;
$socialFacebook = $hasSettings ? app_setting('social_facebook') ?? '#' : '#';
$socialInstagram = $hasSettings ? app_setting('social_instagram') ?? '#' : '#';
@endphp

<footer aria-labelledby="footer-heading" class="bg-dark text-sage relative">
    <h2 id="footer-heading" class="sr-only">{{ __('footer_title') }}</h2>

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
                    <img src="/images/logo/logo.png" alt="{{ $companyName }}" class="h-16 w-auto mx-auto">
                </div>

                <div class="text-ash/80 mb-8 leading-relaxed" v-html="main.settings?.footer_description">
                    {{ __('footer_tagline') }}
                </div>
            </div>

            <!-- Center: Navigation -->
            <nav aria-label="Pagrindinės nuorodos" class="md:col-span-4 md:border-l md:border-ash/20 md:pl-8 text-left">
                <p class="text-xs uppercase tracking-wide text-ash/70 flex items-center gap-2 mb-4">
                    <span class="w-8 h-[2px] bg-brand-primary"></span>
                    // Nuorodos
                </p>
                <div class="grid grid-cols-2 gap-x-6 gap-y-3 footer-nav">
                    <div>
                        <x-footer-link :spa="false" href="https://filamentphp.com/docs" class="text-ash hover:text-sage transition-colors duration-200 footer-nav-link">
                            <svg class="text-ash" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                            </svg>
                            <span>{{ __('footer_documentation') }}</span>
                        </x-footer-link>
                    </div>
                    <div>
                        <x-footer-link :spa="false" href="https://github.com/filamentphp/filament" class="text-ash hover:text-sage transition-colors duration-200 footer-nav-link">
                            <svg class="text-ash" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                            </svg>
                            <span>{{ __('footer_github') }}</span>
                        </x-footer-link>
                    </div>
                    <div>
                        <x-footer-link href="#" class="text-ash hover:text-sage transition-colors duration-200 footer-nav-link">
                            <svg class="text-ash" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                            </svg>
                            <span>{{ __('footer_about') }}</span>
                        </x-footer-link>
                    </div>
                    <div>
                        <x-footer-link href="#" class="text-ash hover:text-sage transition-colors duration-200 footer-nav-link">
                            <svg class="text-ash" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                            </svg>
                            <span>{{ __('footer_secure_payment') }}</span>
                        </x-footer-link>
                    </div>
                    <div>
                        <x-footer-link href="/" class="text-ash hover:text-sage transition-colors duration-200 footer-nav-link">
                            <svg class="text-ash" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                            </svg>
                            <span>{{ __('footer_contact') }}</span>
                        </x-footer-link>
                    </div>
                    @php $features = config('app-features.features'); @endphp
                    @if ((bool) ($features['category'] ?? false))
                        <div>
                            <x-footer-link href="{{ route('localized.categories.index', ['locale' => app()->getLocale()]) }}" class="text-ash hover:text-sage transition-colors duration-200 footer-nav-link">
                                <svg class="text-ash" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                                </svg>
                                <span>{{ __('nav_categories') }}</span>
                            </x-footer-link>
                        </div>
                    @endif
                    @if ((bool) ($features['collection'] ?? false))
                        @php
                            $collectionUrl = \Illuminate\Support\Facades\Route::has('localized.collections.index')
                                ? route('localized.collections.index', ['locale' => app()->getLocale()])
                                : url('/' . app()->getLocale() . '/collections');
                        @endphp
                        <div>
                            <x-footer-link href="{{ $collectionUrl }}" class="text-ash hover:text-sage transition-colors duration-200 footer-nav-link">
                                <svg class="text-ash" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                                </svg>
                                <span>{{ __('nav_collections') }}</span>
                            </x-footer-link>
                        </div>
                    @endif
                    @if ((bool) ($features['brand'] ?? false))
                        <div>
                            <x-footer-link href="{{ route('localized.brands.index', ['locale' => app()->getLocale()]) }}" class="text-ash hover:text-sage transition-colors duration-200 footer-nav-link">
                                <svg class="text-ash" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                                </svg>
                                <span>{{ __('nav_brands') }}</span>
                            </x-footer-link>
                        </div>
                    @endif
                    <div>
                        <x-footer-link href="#" class="text-ash hover:text-sage transition-colors duration-200 footer-nav-link">
                            <svg class="text-ash" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                            </svg>
                            <span>{{ __('legal_shipping') }}</span>
                        </x-footer-link>
                    </div>
                    <div>
                        <x-footer-link href="#" class="text-ash hover:text-sage transition-colors duration-200 footer-nav-link">
                            <svg class="text-ash" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                            </svg>
                            <span>{{ __('footer_returns_refunds') }}</span>
                        </x-footer-link>
                    </div>
                </div>
            </nav>

            <!-- Right: Contacts -->
            <div class="md:col-span-4 md:border-l md:border-ash/20 md:pl-6 text-left">
                <p class="text-xs uppercase tracking-wide text-ash/70 flex items-center gap-2 mb-4">
                    <span class="w-8 h-[2px] bg-brand-primary"></span>
                    // Kontaktai
                </p>
                <div class="space-y-4 footer-contact">
                    @php
                        $phoneDisplay = $companyPhone ?: __('company_phone');
                        $emailDisplay = $companyEmail ?: __('company_email');
                        $contactUrl = \Illuminate\Support\Facades\Route::has('localized.contact')
                            ? route('localized.contact', ['locale' => app()->getLocale()])
                            : '#';
                    @endphp

                    <div class="footer-contact-item flex items-center gap-3">
                        <svg class="text-sage h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M2 5a2 2 0 012-2h2l2 5-2 1a14 14 0 006 6l1-2 5 2v2a2 2 0 01-2 2h-1C9.163 19 5 14.837 5 9V8a2 2 0 00-2-2z" />
                        </svg>
                        <a href="tel:{{ $phoneDisplay }}" class="text-ash hover:text-sage transition-colors duration-200 text-sm">{{ $phoneDisplay }}</a>
                    </div>

                    <div class="footer-contact-item flex items-center gap-3">
                        <svg class="text-sage h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3 8l9 6 9-6M4 6h16a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z" />
                        </svg>
                        <a href="mailto:{{ $emailDisplay }}" class="text-ash hover:text-sage transition-colors duration-200 text-sm">{{ $emailDisplay }}</a>
                    </div>

                    <div class="footer-contact-item flex items-center gap-3">
                        <svg class="text-sage h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                        </svg>
                        <div class="text-ash text-sm">{{ __('footer_hours_desc') }}</div>
                    </div>

                    <!-- Facebook in contacts -->
                    <div class="footer-contact-item flex items-center gap-3">
                        <svg class="text-sage h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                        </svg>
                        <a href="{{ $socialFacebook }}" class="text-ash hover:text-sage transition-colors duration-200 text-sm">Facebook</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Footer -->
    <div class="border-t border-ash/30">
        <div class="max-w-site mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center md:flex md:items-center md:justify-between">
                <div class="text-sm text-ash font-montserrat">&copy; {{ date('Y') }} {{ $companyName }}. {{ __('footer_all_rights_reserved') }}</div>

                <div class="mt-6 md:mt-0">
                    <div class="flex items-center justify-center md:justify-end gap-4">
                        <a href="{{ $socialFacebook }}" class="text-ash/70 hover:text-sage transition-colors duration-200">
                            <span class="sr-only">{{ __('footer_facebook') }}</span>
                            <svg class="text-xl" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="{{ $socialInstagram }}" class="text-ash/70 hover:text-sage transition-colors duration-200">
                            <span class="sr-only">{{ __('footer_instagram') }}</span>
                            <svg class="text-xl" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>