<?php

declare(strict_types=1);

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    // Create admin user for authentication
    $this->adminUser = User::factory()->create([
        'email'    => 'info@egisstatyba.lt',
        'is_admin' => true,
    ]);
});

describe('Admin Panel Integration', function (): void {
    it('loads admin dashboard successfully', function (): void {
        $this->actingAs($this->adminUser)
            ->get('/admin')
            ->assertStatus(200)
            ->assertSee('Dashboard');
    });

    it('displays navigation menu with core resources', function (): void {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin');

        $response->assertStatus(200);

        // Check for key navigation items
        $coreResources = [
            'Products',
            'Categories',
            'Orders',
            'Customers',
            'Inventory',
        ];

        foreach ($coreResources as $resource) {
            $response->assertSee($resource);
        }
    });

    it('authenticates admin users correctly', function (): void {
        // Test successful admin login
        $this->post('/admin/login', [
            'email'    => $this->adminUser->email,
            'password' => 'password',
        ])->assertRedirect('/admin');

        // Test non-admin user rejection
        $regularUser = User::factory()->create(['is_admin' => false]);

        $this->post('/admin/login', [
            'email'    => $regularUser->email,
            'password' => 'password',
        ])->assertSessionHasErrors();
    });

    it('renders dashboard widgets without errors', function (): void {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin');

        $response->assertStatus(200);

        // Check that widgets container is present
        $response->assertSee('widget', false); // Case-insensitive search for widget classes
    });
});

describe('Resource Registration', function (): void {
    it('registers all core e-commerce resources', function (): void {
        $panel = Filament::getPanel('admin');
        $resources = $panel->getResources();

        // Core e-commerce resources that should be registered
        $expectedResources = [
            'App\\Filament\\Resources\\ProductResource',
            'App\\Filament\\Resources\\CategoryResource',
            'App\\Filament\\Resources\\OrderResource',
            'App\\Filament\\Resources\\CustomerResource',
            'App\\Filament\\Resources\\InventoryResource',
            'App\\Filament\\Resources\\BrandResource',
        ];

        foreach ($expectedResources as $expectedResource) {
            expect($resources)->toContain($expectedResource);
        }
    });

    it('discovers resources from correct directories', function (): void {
        $panel = Filament::getPanel('admin');
        $resources = $panel->getResources();

        // Should have discovered many resources (100+)
        expect(count($resources))->toBeGreaterThan(50);

        // All resources should be in the App\Filament\Resources namespace
        foreach ($resources as $resource) {
            expect($resource)->toStartWith('App\\Filament\\Resources\\');
        }
    });
});

describe('Multi-language Support', function (): void {
    it('supports Lithuanian as default language', function (): void {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin');

        $response->assertStatus(200);

        // Check that Lithuanian locale is active
        expect(app()->getLocale())->toBe('lt');
    });

    it('allows language switching in admin panel', function (): void {
        // Test language switch endpoint
        $response = $this->actingAs($this->adminUser)
            ->post('/admin/language/switch', ['language' => 'en']);

        $response->assertStatus(302); // Redirect after language switch
    });

    it('loads translation files for supported languages', function (): void {
        $supportedLanguages = ['lt', 'en', 'de', 'ru'];

        foreach ($supportedLanguages as $lang) {
            // Check that translation files exist
            $langPath = resource_path("lang/{$lang}");
            expect(file_exists($langPath))->toBeTrue("Translation directory for {$lang} should exist");
        }
    });
});

describe('Configuration Integrity', function (): void {
    it('loads Filament configuration without errors', function (): void {
        $config = config('filament');

        expect($config)->toBeArray();
        expect($config)->toHaveKey('default_filesystem_disk');
    });

    it('has proper panel configuration', function (): void {
        $panel = Filament::getPanel('admin');

        expect($panel->getId())->toBe('admin');
        expect($panel->getPath())->toBe('/admin');
    });

    it('configures middleware correctly', function (): void {
        $panel = Filament::getPanel('admin');
        $middleware = $panel->getMiddleware();

        // Should include essential Laravel middleware
        $requiredMiddleware = [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ];

        foreach ($requiredMiddleware as $required) {
            expect($middleware)->toContain($required);
        }
    });
});
