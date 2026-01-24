<?php

declare(strict_types=1);

use App\Filament\AdminPanelProvider;
use Filament\Panel;

describe('Admin Panel Configuration', function (): void {
    it('panel loads with correct authentication guard', function (): void {
        $provider = new AdminPanelProvider(app());
        $panel = $provider->panel(Panel::make());

        expect($panel->getAuthGuard())->toBe('admin');
        expect($panel->getAuthPasswordBroker())->toBe('admin_users');
    });

    it('panel has correct basic configuration', function (): void {
        $provider = new AdminPanelProvider(app());
        $panel = $provider->panel(Panel::make());

        expect($panel->getId())->toBe('admin');
        expect($panel->getPath())->toBe('admin');
        expect($panel->getHomeUrl())->toStartWith('/admin');
        expect($panel->hasTopbar())->toBeFalse();
    });

    it('panel configures authentication middleware correctly', function (): void {
        $provider = new AdminPanelProvider(app());
        $panel = $provider->panel(Panel::make());

        $authMiddleware = $panel->getAuthMiddleware();
        expect($authMiddleware)->toContain(\Filament\Http\Middleware\Authenticate::class);
    });

    it('panel includes required middleware stack', function (): void {
        $provider = new AdminPanelProvider(app());
        $panel = $provider->panel(Panel::make());

        $middleware = $panel->getMiddleware();

        $requiredMiddleware = [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Filament\Http\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Filament\Http\Middleware\DispatchServingFilamentEvent::class,
        ];

        foreach ($requiredMiddleware as $required) {
            expect($middleware)->toContain($required);
        }
    });

    it('panel uses the default Filament login page', function (): void {
        $provider = new AdminPanelProvider(app());
        $panel = $provider->panel(Panel::make());

        expect($panel->getLoginRouteAction())->toBe(\Filament\Auth\Pages\Login::class);
    });
});

describe('Admin URL Authentication', function (): void {
    it('admin URL redirects unauthenticated users to login route', function (): void {
        $response = $this->get('/admin');

        // Should redirect to the Filament admin login route
        $response->assertRedirect(route('filament.admin.auth.login'));
    });

    it('admin authentication middleware redirects to correct login route', function (): void {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect();

        // Should redirect to Filament admin login
        if (route('filament.admin.auth.login')) {
            $response->assertRedirect(route('filament.admin.auth.login'));
        }
    });
});

describe('Panel Resource Discovery', function (): void {
    it('panel discovers resources from correct directory', function (): void {
        $provider = new AdminPanelProvider(app());
        $panel = $provider->panel(Panel::make());

        // In testing environment, should still discover resources
        $resources = $panel->getResources();
        expect($resources)->toBeArray();

        // Should have at least some resources discovered
        expect(count($resources))->toBeGreaterThan(0);
    });

    it('panel discovers pages from correct directory', function (): void {
        $provider = new AdminPanelProvider(app());
        $panel = $provider->panel(Panel::make());

        $pages = $panel->getPages();
        expect($pages)->toBeArray();

        // In testing environment, should include the dashboard page
        if (app()->environment('testing')) {
            expect($pages)->toContain(\App\Filament\Pages\Dashboard::class);
        }
    });

    it('panel configures widgets correctly for testing environment', function (): void {
        // Ensure we're in testing environment
        app()->instance('env', 'testing');

        $provider = new AdminPanelProvider(app());
        $panel = $provider->panel(Panel::make());

        $widgets = $panel->getWidgets();
        expect($widgets)->toBeArray();

        // Should include testing widgets
        $expectedTestingWidgets = [
            \App\Filament\Widgets\DashboardKpiWidget::class,
            \App\Filament\Widgets\DashboardTimeSeriesWidget::class,
            \App\Filament\Widgets\DashboardRecentOrdersTable::class,
        ];

        foreach ($expectedTestingWidgets as $widget) {
            expect($widgets)->toContain($widget);
        }
    });
});
