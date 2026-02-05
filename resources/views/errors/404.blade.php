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
                            static fn (array $category): bool => $category['label'] !== '' && filled($category['url']),
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

    $localizedSupportEmail = __('messages.company_email');
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
                    'label' => __('frontend.errors.actions.contact_support'),
                    'url' => $supportUrl,
                    'style' => 'primary',
                ],
                $resolvedSupportEmail
                    ? [
                        'label' => __('frontend.errors.404.actions.email_us'),
                        'url' => 'mailto:' . $resolvedSupportEmail,
                        'style' => 'secondary',
                    ]
                    : null,
                Route::has('localized.contact.index')
                    ? [
                        'label' => __('frontend.errors.404.actions.visit_contact'),
                        'url' => $contactUrl . '#contact-form',
                        'style' => 'secondary',
                    ]
                    : null,
            ],
            static fn ($action) => is_array($action) && filled($action['url'] ?? null),
        ),
    );
@endphp

@extends('errors.4xx', [
    'code' => '404',
    'title' => __('frontend.errors.404.title'),
    'description' => __('frontend.errors.404.description'),
    'showSearch' => true,
    'searchTitle' => __('frontend.errors.404.search_title'),
    'searchPlaceholder' => __('frontend.errors.404.search_placeholder'),
    'topCategories' => $topCategories->toArray(),
    'topCategoriesTitle' => __('frontend.errors.404.top_categories_title'),
    'primaryAction' => [
        'label' => __('frontend.errors.actions.go_home'),
        'url' => route('localized.home', ['locale' => $locale]) ?? url('/'),
    ],
    'secondaryAction' => [
        'label' => __('frontend.errors.actions.go_back'),
        'type' => 'back',
    ],
    'supportTitle' => __('frontend.errors.404.support_title'),
    'supportDescription' => __('frontend.errors.404.support_description'),
    'contactCta' => [
        'title' => __('frontend.errors.404.contact_cta.title'),
        'description' => __('frontend.errors.404.contact_cta.description'),
        'actions' => $contactActions,
    ],
    'links' => [
        [
            'label' => __('frontend.errors.actions.browse_categories'),
            'url' => route('localized.categories.index', ['locale' => $locale]),
            'icon' => 'categories',
        ],
        [
            'label' => __('frontend.errors.actions.shop_products'),
            'url' => Route::has('frontend.products.index') ? route('frontend.products.index', ['locale' => $locale]) : url('/products'),
            'icon' => 'products',
        ],
        [
            'label' => __('frontend.errors.actions.discover_brands'),
            'url' => route('localized.brands.index', ['locale' => $locale]),
            'icon' => 'brands',
        ],
        [
            'label' => __('frontend.errors.actions.view_cart'),
            'url' => Route::has('frontend.cart.index') ? route('frontend.cart.index') : url('/cart'),
            'icon' => 'cart',
        ],
    ],
])
