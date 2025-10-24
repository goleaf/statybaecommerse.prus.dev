@php
    use App\Models\Category;
    use App\Services\Shared\CacheService;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use Throwable;

    $locale = app()->getLocale();
    $cacheService = app(CacheService::class);
    $hasCategoryRoute = Route::has('localized.categories.show');

    $topCategories = collect();

    if ($hasCategoryRoute) {
        try {
            $topCategories = collect(
                $cacheService->rememberLong("errors.404.top_categories.$locale", static function () use ($locale) {
                    return Category::query()
                        ->select(['id', 'slug', 'name', 'short_description'])
                        ->with([
                            'translations' => static function ($query) use ($locale): void {
                                $query->whereIn('locale', [$locale, config('app.locale', 'en')]);
                            },
                        ])
                        ->withCount('products')
                        ->whereNull('parent_id')
                        ->where('is_visible', true)
                        ->orderByDesc('products_count')
                        ->orderBy('name')
                        ->limit(6)
                        ->get()
                        ->map(static function (Category $category) use ($locale) {
                            $label = (string) ($category->trans('name', $locale) ?? ($category->name ?? ''));
                            $description =
                                (string) ($category->trans('short_description', $locale) ??
                                    ($category->short_description ?? ''));

                            return [
                                'label' => $label,
                                'description' => Str::limit(trim($description), 80),
                                'url' => route('localized.categories.show', [
                                    'locale' => $locale,
                                    'category' => $category->slug,
                                ]),
                                'product_count' => (int) ($category->products_count ?? 0),
                            ];
                        })
                        ->filter(
                            static fn(array $category): bool => $category['label'] !== '' && filled($category['url']),
                        )
                        ->values()
                        ->toArray();
                }),
            );
        } catch (Throwable $exception) {
            report($exception);
            $topCategories = collect();
        }
    }

    $topCategories = $topCategories->take(4);

    $localizedSupportEmail = __('company_email');
    $fallbackSupportEmail = config('mail.from.address', 'support@example.com');
    $resolvedSupportEmail = $localizedSupportEmail !== 'company_email' ? $localizedSupportEmail : $fallbackSupportEmail;

    $contactUrl = Route::has('localized.contact.index')
        ? route('localized.contact.index', ['locale' => $locale])
        : url('/contact');

    $supportUrl = Route::has('localized.support.index')
        ? route('localized.support.index', ['locale' => $locale])
        : url('/support');

    $contactActions = array_values(
        array_filter(
            [
                [
                    'label' => __('Contact Support'),
                    'url' => $supportUrl,
                    'style' => 'primary',
                ],
                $resolvedSupportEmail
                    ? [
                        'label' => __('Email Us'),
                        'url' => 'mailto:' . $resolvedSupportEmail,
                        'style' => 'secondary',
                    ]
                    : null,
                Route::has('localized.contact.index')
                    ? [
                        'label' => __('Visit Contact Page'),
                        'url' => $contactUrl . '#contact-form',
                        'style' => 'secondary',
                    ]
                    : null,
            ],
            static fn($action) => is_array($action) && filled($action['url'] ?? null),
        ),
    );
@endphp

@extends('errors.4xx', [
    'code' => '404',
    'title' => __('We couldn\'t find that page'),
    'description' => __('The page you are looking for may have been moved or no longer exists. Double-check the address or explore one of the helpful links below.'),
    'showSearch' => true,
    'searchTitle' => __('Search our catalog'),
    'searchPlaceholder' => __('Search for products, brands, or help'),
    'topCategories' => $topCategories->toArray(),
    'topCategoriesTitle' => __('Explore top categories'),
    'primaryAction' => [
        'label' => __('Go Home'),
        'url' => route('localized.home', ['locale' => $locale]) ?? url('/'),
    ],
    'secondaryAction' => [
        'label' => __('Go Back'),
        'type' => 'back',
    ],
    'supportTitle' => __('Need directions?'),
    'supportDescription' => __('Share the reference ID below with our support team and we\'ll help you get to the right place.'),
    'contactCta' => [
        'title' => __('Need a personal recommendation?'),
        'description' => __('Our team can guide you to the right product or answer questions about availability and delivery.'),
        'actions' => $contactActions,
    ],
    'links' => [
        [
            'label' => __('Browse Categories'),
            'url' => route('localized.categories.index', ['locale' => $locale]),
            'icon' => 'categories',
        ],
        [
            'label' => __('Shop Products'),
            'url' => Route::has('frontend.products.index') ? route('frontend.products.index', ['locale' => $locale]) : url('/products'),
            'icon' => 'products',
        ],
        [
            'label' => __('Discover Brands'),
            'url' => route('localized.brands.index', ['locale' => $locale]),
            'icon' => 'brands',
        ],
        [
            'label' => __('View Cart'),
            'url' => Route::has('frontend.cart.index') ? route('frontend.cart.index') : url('/cart'),
            'icon' => 'cart',
        ],
    ],
])
