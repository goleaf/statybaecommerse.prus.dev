<?php

declare(strict_types=1);

namespace App\Support\Frontend;

use Illuminate\Support\Facades\Route;

final class InfoPages
{
    /**
     * @return array<int, string>
     */
    public static function staticPageKeys(): array
    {
        return [
            'faq',
            'payment-methods',
            'popular-products',
            'building-materials',
            'tools-equipment',
            'special-offers',
            'services-for-craftsmen',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function get(string $key): ?array
    {
        $page = trans(sprintf('info_pages.pages.%s', $key), [], 'lt');

        return is_array($page) ? $page : null;
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<int, array{title: string, description: string|null, url: string}>
     */
    public static function resolveRelatedPages(array $keys): array
    {
        $pages = [];

        foreach ($keys as $key) {
            $page = self::get($key);
            $url = self::resolvePageUrl($key);

            if ($page === null || $url === null) {
                continue;
            }

            $pages[] = [
                'title' => (string) ($page['title'] ?? $key),
                'description' => isset($page['description']) && is_string($page['description'])
                    ? $page['description']
                    : null,
                'url' => $url,
            ];
        }

        return $pages;
    }

    /**
     * @param  array<int, array<string, mixed>>  $actions
     * @return array<int, array{label: string, url: string, style: string}>
     */
    public static function resolveActions(array $actions): array
    {
        $resolved = [];

        foreach ($actions as $action) {
            $label = $action['label'] ?? null;
            $style = $action['style'] ?? 'primary';
            $url = self::resolveActionUrl($action);

            if (! is_string($label) || $label === '' || ! is_string($style) || $style === '' || $url === null) {
                continue;
            }

            $resolved[] = [
                'label' => $label,
                'url' => $url,
                'style' => $style,
            ];
        }

        return $resolved;
    }

    public static function resolvePageUrl(string $key): ?string
    {
        return match ($key) {
            'faq' => self::routeUrl(['localized.info.faq', 'frontend.info.faq']),
            'payment-methods' => self::routeUrl(['localized.info.payment-methods', 'frontend.info.payment-methods']),
            'popular-products' => self::routeUrl(['localized.info.popular-products', 'frontend.info.popular-products']),
            'building-materials' => self::routeUrl(['localized.info.building-materials', 'frontend.info.building-materials']),
            'tools-equipment' => self::routeUrl(['localized.info.tools-equipment', 'frontend.info.tools-equipment']),
            'special-offers' => self::routeUrl(['localized.info.special-offers', 'frontend.info.special-offers']),
            'services-for-craftsmen' => self::routeUrl(['localized.info.services-for-craftsmen', 'frontend.info.services-for-craftsmen']),
            'privacy' => self::routeUrl(['localized.legal.privacy']),
            'terms' => self::routeUrl(['localized.legal.terms']),
            'shipping' => self::routeUrl(['localized.legal.shipping']),
            'returns' => self::routeUrl(['localized.legal.returns']),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $action
     */
    private static function resolveActionUrl(array $action): ?string
    {
        $type = $action['type'] ?? null;

        if ($type === 'page' && isset($action['page']) && is_string($action['page'])) {
            return self::resolvePageUrl($action['page']);
        }

        if ($type === 'route') {
            $routeNames = $action['routes'] ?? null;

            if (is_string($routeNames)) {
                $routeNames = [$routeNames];
            }

            if (is_array($routeNames)) {
                /** @var array<int, string> $routeNames */
                return self::routeUrl($routeNames);
            }
        }

        if ($type === 'url' && isset($action['url']) && is_string($action['url']) && $action['url'] !== '') {
            return $action['url'];
        }

        return null;
    }

    /**
     * @param  array<int, string>  $routeNames
     */
    private static function routeUrl(array $routeNames): ?string
    {
        foreach ($routeNames as $routeName) {
            if (Route::has($routeName)) {
                return route($routeName);
            }
        }

        return null;
    }
}
