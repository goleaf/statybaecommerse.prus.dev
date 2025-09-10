@php($hasSettings = \Illuminate\Support\Facades\Schema::hasTable('settings'))
@php($companyName = $hasSettings ? app_setting('company_name') ?? config('app.name') : config('app.name'))
@php($companyEmail = $hasSettings ? app_setting('email') ?? null : null)
@php($companyPhone = $hasSettings ? app_setting('phone_number') ?? null : null)
@php($companyAddress = $hasSettings ? app_setting('company_address') ?? null : null)
@php($socialFacebook = $hasSettings ? app_setting('social_facebook') ?? '#' : '#')
@php($socialInstagram = $hasSettings ? app_setting('social_instagram') ?? '#' : '#')

<footer aria-labelledby="footer-heading" class="bg-white border-t border-gray-100">
    <h2 id="footer-heading" class="sr-only">{{ __('footer_heading') }}</h2>
    <x-container>
        <div class="grid grid-cols-1 gap-8 py-10 lg:grid-cols-3 lg:py-20">
            <div class="lg:max-w-sm">
                <h3 class="text-sm font-medium text-gray-900">
                    <x-brand
                             class="w-auto h-12 lg:h-14 text-gray-500"
                             aria-hidden="true" />
                </h3>
                <p class="mt-10 text-gray-500 text-sm/6">
                    {{ __('footer_tagline') }}
                </p>
            </div>
            <div class="space-y-12 lg:grid lg:grid-cols-2 lg:gap-8 lg:space-y-0 lg:col-span-2">
                <div class="grid grid-cols-2 gap-8 lg:gap-12">
                    <div>
                        <h3 class="font-heading text-sm font-medium uppercase tracking-wider text-gray-500">
                            {{ __('footer_about') }}
                        </h3>
                        <ul role="list" class="mt-10 space-y-5">
                            <li>
                                <x-footer-link :spa="false" href="https://filamentphp.com/docs">
                                    {{ __('footer_documentation') }}
                                </x-footer-link>
                            </li>
                            <li>
                                <x-footer-link :spa="false" href="https://github.com/filamentphp/filament">
                                    {{ __('footer_github') }}
                                </x-footer-link>
                            </li>
                            <li>
                                <x-footer-link href="#">{{ __('footer_about') }}</x-footer-link>
                            </li>
                            <li>
                                <x-footer-link href="#">{{ __('footer_secure_payment') }}</x-footer-link>
                            </li>
                            <li>
                                <x-footer-link href="/">{{ __('footer_contact') }}</x-footer-link>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-heading text-sm font-medium uppercase tracking-wider text-gray-500">
                            {{ __('footer_shop') }}
                        </h3>
                        <ul role="list" class="mt-10 space-y-5">
                            @php($features = config('app-features.features'))
                            @if ((bool) ($features['category'] ?? false))
                                <li>
                                    <x-footer-link
                                                   href="{{ route('categories.index', ['locale' => app()->getLocale()]) }}">{{ __('nav_categories') }}</x-footer-link>
                                </li>
                            @endif
                            @if ((bool) ($features['collection'] ?? false) && \Illuminate\Support\Facades\Route::has('collections.index'))
                                <li>
                                    <x-footer-link
                                                   href="{{ route('collections.index', ['locale' => app()->getLocale()]) }}">{{ __('nav_collections') }}</x-footer-link>
                                </li>
                            @endif
                            @if ((bool) ($features['brand'] ?? false))
                                <li>
                                    <x-footer-link
                                                   href="{{ route('brands.index', ['locale' => app()->getLocale()]) }}">{{ __('nav_brands') }}</x-footer-link>
                                </li>
                            @endif
                            <li>
                                <x-footer-link href="#">{{ __('legal_shipping') }}</x-footer-link>
                            </li>
                            <li>
                                <x-footer-link href="#">{{ __('footer_returns_refunds') }}</x-footer-link>
                            </li>
                        </ul>
                    </div>
                </div>
                <div>
                    <h3 class="font-heading text-sm font-medium uppercase tracking-wider text-gray-500">
                        {{ __('footer_subscribe_title') }}
                    </h3>
                    <div class="mt-10 space-y-6">
                        <div>
                            <p class="text-sm leading-6 text-gray-500">
                                {{ __('footer_subscribe_desc') }}
                            </p>
                            {{-- Enhanced Newsletter Form --}}
                            <form class="mt-5" target="_blank" novalidate>
                                <div class="space-y-2">
                                    <label for="newsletter-email"
                                           class="block text-sm font-medium text-gray-700">{{ __('auth_email') }}</label>
                                    <div class="flex gap-3">
                                        <input
                                               type="email"
                                               id="newsletter-email"
                                               name="EMAIL"
                                               placeholder="{{ __('footer_enter_email') }}"
                                               class="flex-1 rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                                               required />
                                        <button
                                                type="submit"
                                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                                            {{ __('footer_subscribe_button') }}
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <!--End mc_embed_signup-->
                        </div>
                        <div class="flex items-center space-x-4">
                            <a
                               href="{{ $socialFacebook }}"
                               class="text-primary-500 hover:text-primary-700">
                                <span class="sr-only">{{ __('footer_facebook') }}</span>
                                <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                          fill-rule="evenodd"
                                          d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                                          clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a href="{{ $socialInstagram }}" class="text-primary-500 hover:text-primary-700">
                                <span class="sr-only">{{ __('footer_instagram') }}</span>
                                <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                          fill-rule="evenodd"
                                          d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"
                                          clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="pb-10 lg:flex lg:items-center lg:justify-between lg:pb-20">
            <div class="space-y-4 lg:space-y-0 lg:flex lg:flex-1 lg:gap-10">
                <div>
                    <dl class="font-heading text-sm/4 font-semibold uppercase tracking-wider text-gray-900 underline">
                        {{ __('footer_contact') }}
                    </dl>
                    <ul class="mt-4 flex flex-col space-y-1 text-sm text-gray-900">
                        @if ($companyPhone)
                            <li class="flex items-center gap-3">
                                <svg class="size-5 text-gray-500" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M2 5a2 2 0 012-2h2l2 5-2 1a14 14 0 006 6l1-2 5 2v2a2 2 0 01-2 2h-1C9.163 19 5 14.837 5 9V8a2 2 0 00-2-2z" />
                                </svg>
                                <a href="tel:{{ $companyPhone }}" class="hover:underline">
                                    {{ $companyPhone }}
                                </a>
                            </li>
                        @endif

                        @if ($companyEmail)
                            <li class="flex items-center gap-3">
                                <svg class="size-5 text-gray-500" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3 8l9 6 9-6M4 6h16a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z" />
                                </svg>
                                <a href="mailto:{{ $companyEmail }}" class="hover:underline">
                                    {{ $companyEmail }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
                <div>
                    <dl class="font-heading text-sm/4 font-semibold uppercase tracking-wider text-gray-900 underline">
                        {{ __('footer_hours') }}
                    </dl>
                    <dt class="mt-4 text-sm leading-5 text-gray-900">
                        {{ __('footer_hours_desc') }}
                    </dt>
                </div>
            </div>

            @if (session()->has('zone'))
                <!-- Zones Selector -->
                <x-zones-selector />
            @endif
        </div>
        <div class="flex flex-col items-center border-t border-gray-200 py-8 sm:flex-row sm:justify-between lg:py-10">
            <p class="text-sm text-gray-700">
                © {{ date('Y') }} {{ config('app.name') }}, Inc. {{ __('footer_all_rights_reserved') }}
                <a href="https://filamentphp.com" target="_blank" class="pl-1 underline hover:text-gray-900">
                    {{ __('footer_powered_by_filament') }}
                </a>
            </p>
            <div class="mt-5 flex items-center gap-6 divide-x divide-gray-200 sm:mt-0">
                <x-language-switcher />
                @if (auth()->check() && auth()->user()->can('view orders'))
                    <x-link href="{{ route('exports.index') }}"
                            class="inline-flex px-3 text-sm leading-5 text-gray-700 hover:underline">
                        {{ __('Exports') }}
                    </x-link>
                @endif
                @php($hasLegals = \Illuminate\Support\Facades\Schema::hasTable('legals'))
                @if ($hasLegals)
                    @php($legalModel = app(\App\Models\Legal::class))
                    @php($privacy = $legalModel->newQuery()->where('slug', 'privacy')->where('is_enabled', true)->first())
                    @php($terms = $legalModel->newQuery()->where('slug', 'terms')->where('is_enabled', true)->first())
                    @php($refund = $legalModel->newQuery()->where('slug', 'refund')->where('is_enabled', true)->first())
                    @php($shipping = $legalModel->newQuery()->where('slug', 'shipping')->where('is_enabled', true)->first())
                    @if ($privacy)
                        <x-link href="{{ route('legal.show', ['locale' => app()->getLocale(), 'slug' => $privacy->trans('slug') ?? 'privacy']) }}"
                                class="inline-flex px-3 text-sm leading-5 text-gray-700 hover:underline">
                            {{ __('legal_privacy') }}
                        </x-link>
                    @endif
                    @if ($terms)
                        <x-link href="{{ route('legal.show', ['locale' => app()->getLocale(), 'slug' => $terms->trans('slug') ?? 'terms']) }}"
                                class="inline-flex px-3 text-sm leading-5 text-gray-700 hover:underline">
                            {{ __('legal_terms') }}
                        </x-link>
                    @endif
                    @if ($refund)
                        <x-link href="{{ route('legal.show', ['locale' => app()->getLocale(), 'slug' => $refund->trans('slug') ?? 'refund']) }}"
                                class="inline-flex px-3 text-sm leading-5 text-gray-700 hover:underline">
                            {{ __('legal_refund') }}
                        </x-link>
                    @endif
                    @if ($shipping)
                        <x-link href="{{ route('legal.show', ['locale' => app()->getLocale(), 'slug' => $shipping->trans('slug') ?? 'shipping']) }}"
                                class="inline-flex px-3 text-sm leading-5 text-gray-700 hover:underline">
                            {{ __('legal_shipping') }}
                        </x-link>
                    @endif
                @endif
            </div>
        </div>
    </x-container>
    {{-- Organization JSON-LD for SEO --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Organization",
        "name": "{{ $companyName }}",
        "url": "{{ url('/') }}",
        "email": {{ $companyEmail ? '"' . e($companyEmail) . '"' : 'null' }},
        "telephone": {{ $companyPhone ? '"' . e($companyPhone) . '"' : 'null' }},
        "address": {{ $companyAddress ? '"' . e($companyAddress) . '"' : 'null' }},
        "sameAs": [
            {{ $socialFacebook ? '"' . e($socialFacebook) . '"' : '' }}{{ ($socialFacebook && $socialInstagram) ? ',' : '' }}
            {{ $socialInstagram ? '"' . e($socialInstagram) . '"' : '' }}
        ]
    }
    </script>
</footer>
