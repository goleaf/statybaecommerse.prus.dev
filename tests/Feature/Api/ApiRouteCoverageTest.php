<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Generator;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route as RouteFacade;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ApiRouteCoverageTest extends TestCase
{
    #[DataProvider('apiRouteProvider')]
    public function test_api_route_is_registered(string $method, string $uri, ?string $name): void
    {
        // Collect the current API routes so we can assert against the real router state.
        $matchingRoute = $this->apiRoutes()
            ->first(fn (Route $route): bool => $this->routeMatches($route, $method, $uri));

        // Guard against regressions where a defined URI or verb silently disappears from the router.
        $this->assertNotNull(
            $matchingRoute,
            sprintf('Failed asserting that the %s %s route is registered.', $method, $uri),
        );

        if ($name !== null) {
            // Confirm the named routes keep their contract so downstream URL generation stays stable.
            $this->assertSame($name, (string) $matchingRoute->getName());
        }
    }

    /**
     * @return Generator<string, array{0: string, 1: string, 2: ?string}>
     */
    public static function apiRouteProvider(): Generator
    {
        // Payment webhooks ensure external providers can push events into our system.
        yield 'webhooks.stripe' => ['POST', 'api/webhooks/stripe', 'webhooks.stripe'];
        yield 'webhooks.notchpay' => ['POST', 'api/webhooks/notchpay', 'webhooks.notchpay'];

        // Notification endpoints for the primary namespace.
        yield 'notifications.index' => ['GET', 'api/notifications', 'api.notifications.index'];
        yield 'notifications.stats' => ['GET', 'api/notifications/stats', 'api.notifications.stats'];
        yield 'notifications.search' => ['GET', 'api/notifications/search', 'api.notifications.search'];
        yield 'notifications.show' => ['GET', 'api/notifications/{notification}', 'api.notifications.show'];
        yield 'notifications.mark-all-read' => ['POST', 'api/notifications/mark-all-read', 'api.notifications.mark-all-read'];
        yield 'notifications.mark-all-unread' => ['POST', 'api/notifications/mark-all-unread', 'api.notifications.mark-all-unread'];
        yield 'notifications.mark-read' => ['POST', 'api/notifications/{notification}/mark-read', 'api.notifications.mark-as-read'];
        yield 'notifications.mark-unread' => ['POST', 'api/notifications/{notification}/mark-unread', 'api.notifications.mark-as-unread'];
        yield 'notifications.destroy' => ['DELETE', 'api/notifications/{notification}', 'api.notifications.destroy'];

        // Catalog product listings remain the backbone of the public storefront.
        yield 'products.index' => ['GET', 'api/products', 'api.products.index'];
        yield 'products.search' => ['GET', 'api/products/search', 'api.products.search'];
        yield 'products.catalog' => ['GET', 'api/products/catalog', 'api.products.catalog'];
        yield 'products.show' => ['GET', 'api/products/{product}', 'api.products.show'];

        // Category browsing routes keep taxonomy data synchronized.
        yield 'categories.index' => ['GET', 'api/categories', 'api.categories.index'];
        yield 'categories.tree' => ['GET', 'api/categories/tree', 'api.categories.tree'];
        yield 'categories.show' => ['GET', 'api/categories/{category}', 'api.categories.show'];

        // Order lookup requires authentication and a tolerant identifier pattern.
        yield 'orders.show' => ['GET', 'api/orders/{order}', 'api.orders.show'];

        // Versioned health endpoints keep monitoring integrations straightforward.
        yield 'v1.health' => ['GET', 'api/v1/health', 'api.v1.health'];
        yield 'v1.ready' => ['GET', 'api/v1/ready', 'api.v1.ready'];
        yield 'v1.search' => ['GET', 'api/v1/search', 'api.v1.search'];
        yield 'v1.user' => ['GET', 'api/v1/user', 'api.v1.user.show'];
        yield 'v1.autocomplete' => ['POST', 'api/v1/autocomplete-search', 'api.v1.autocomplete.search'];

        // Versioned notification routes mirror the primary namespace for backward compatibility.
        yield 'v1.notifications.index' => ['GET', 'api/v1/notifications', 'api.v1.notifications.index'];
        yield 'v1.notifications.stats' => ['GET', 'api/v1/notifications/stats', 'api.v1.notifications.stats'];
        yield 'v1.notifications.search' => ['GET', 'api/v1/notifications/search', 'api.v1.notifications.search'];
        yield 'v1.notifications.show' => ['GET', 'api/v1/notifications/{notification}', 'api.v1.notifications.show'];
        yield 'v1.notifications.mark-all-read' => ['POST', 'api/v1/notifications/mark-all-read', 'api.v1.notifications.mark-all-read'];
        yield 'v1.notifications.mark-all-unread' => ['POST', 'api/v1/notifications/mark-all-unread', 'api.v1.notifications.mark-all-unread'];
        yield 'v1.notifications.mark-read' => ['POST', 'api/v1/notifications/{notification}/mark-read', 'api.v1.notifications.mark-as-read'];
        yield 'v1.notifications.mark-unread' => ['POST', 'api/v1/notifications/{notification}/mark-unread', 'api.v1.notifications.mark-as-unread'];
        yield 'v1.notifications.destroy' => ['DELETE', 'api/v1/notifications/{notification}', 'api.v1.notifications.destroy'];

        // Export downloads use signed URLs so we lock their presence down as well.
        yield 'exports.download' => ['GET', 'api/exports/download/{export}', 'api.exports.download'];

        // Partner API routes expose fulfillment integrations.
        yield 'partner.ping' => ['GET', 'api/partner/ping', 'api.partner.ping'];
        yield 'partner.inventory' => ['GET', 'api/partner/inventory', 'api.partner.inventory.index'];
        yield 'partner.orders' => ['GET', 'api/partner/orders', 'api.partner.orders.index'];

        // Audit log listing is restricted and should stay routable for permission checks.
        yield 'audit-logs.index' => ['GET', 'api/audit-logs', 'api.audit-logs.index'];

        // Product history admin routes power the merchandising audit UI.
        yield 'admin.product-histories.index' => ['GET', 'api/admin/products/{product}/histories', 'api.admin.product-histories.index'];
        yield 'admin.product-histories.statistics' => ['GET', 'api/admin/products/{product}/histories/statistics', 'api.admin.product-histories.statistics'];
        yield 'admin.product-histories.show' => ['GET', 'api/admin/products/{product}/histories/{history}', 'api.admin.product-histories.show'];
        yield 'admin.product-histories.store' => ['POST', 'api/admin/products/{product}/histories', 'api.admin.product-histories.store'];
        yield 'admin.product-histories.export' => ['POST', 'api/admin/products/{product}/histories/export', 'api.admin.product-histories.export'];

        // Campaign click legacy routes remain until the new namespace ships.
        yield 'campaign-clicks.index' => ['GET', 'api/campaign-clicks', null];
        yield 'campaign-clicks.store' => ['POST', 'api/campaign-clicks', null];
        yield 'campaign-clicks.statistics' => ['GET', 'api/campaign-clicks/statistics', null];
        yield 'campaign-clicks.analytics' => ['GET', 'api/campaign-clicks/analytics', null];
        yield 'campaign-clicks.export' => ['GET', 'api/campaign-clicks/export', null];
        yield 'campaign-clicks.show' => ['GET', 'api/campaign-clicks/{campaignClick}', null];
        yield 'campaign-clicks.update' => ['PUT', 'api/campaign-clicks/{campaignClick}', null];
        yield 'campaign-clicks.destroy' => ['DELETE', 'api/campaign-clicks/{campaignClick}', null];
        yield 'campaigns.clicks.index' => ['GET', 'api/campaigns/{campaign}/clicks', null];
        yield 'my.campaign-clicks.index' => ['GET', 'api/my/campaign-clicks', null];
    }

    /**
     * @return Collection<int, Route>
     */
    private function apiRoutes(): Collection
    {
        // Filter the router table down to only URIs inside the API prefix so assertions stay focused.
        return Collection::make(RouteFacade::getRoutes())
            ->filter(static fn (Route $route): bool => str_starts_with($route->uri(), 'api/'))
            ->values();
    }

    private function routeMatches(Route $route, string $method, string $uri): bool
    {
        // HEAD appears automatically for GET routes, so we ignore it when matching by verb.
        $methods = array_filter($route->methods(), static fn (string $registeredMethod): bool => $registeredMethod !== 'HEAD');

        // Match on both URI and method to avoid collisions between similar endpoints.
        return $route->uri() === $uri && in_array($method, $methods, true);
    }
}
