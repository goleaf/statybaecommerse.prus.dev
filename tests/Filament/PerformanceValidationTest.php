<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->adminUser = User::factory()->create([
        'email'    => 'info@egisstatyba.lt',
        'is_admin' => true,
    ]);
});

describe('Admin Panel Performance', function (): void {
    it('loads admin dashboard within acceptable time', function (): void {
        $startTime = microtime(true);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);

        // Should load within 3 seconds
        expect($executionTime)->toBeLessThan(3.0);
    });

    it('handles resource list pagination efficiently', function (): void {
        $startTime = microtime(true);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/products');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);

        // Resource lists should load within 2 seconds
        expect($executionTime)->toBeLessThan(2.0);
    });

    it('maintains acceptable memory usage during admin operations', function (): void {
        $initialMemory = memory_get_usage(true);

        // Perform multiple admin operations
        $this->actingAs($this->adminUser)
            ->get('/admin')
            ->assertStatus(200);

        $this->actingAs($this->adminUser)
            ->get('/admin/products')
            ->assertStatus(200);

        $this->actingAs($this->adminUser)
            ->get('/admin/orders')
            ->assertStatus(200);

        $finalMemory = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;

        // Memory increase should be reasonable (less than 50MB)
        expect($memoryIncrease)->toBeLessThan(50 * 1024 * 1024);
    });
});

describe('System Compatibility', function (): void {
    it('verifies PHP version compatibility', function (): void {
        $phpVersion = PHP_VERSION;

        // Should be running PHP 8.1 or higher
        expect(version_compare($phpVersion, '8.1.0', '>='))->toBeTrue();
    });

    it('verifies Laravel version compatibility', function (): void {
        $laravelVersion = app()->version();

        // Should be running Laravel 12.x
        expect($laravelVersion)->toStartWith('12.');
    });

    it('verifies Filament version compatibility', function (): void {
        // Check that Filament classes are available
        expect(class_exists(\Filament\Panel::class))->toBeTrue();
        expect(class_exists(\Filament\Resources\Resource::class))->toBeTrue();
    });

    it('verifies database connection is working', function (): void {
        // Test database connectivity
        $result = \DB::select('SELECT 1 as test');

        expect($result)->toHaveCount(1);
        expect($result[0]->test)->toBe(1);
    });

    it('verifies cache system is functional', function (): void {
        $testKey = 'test_cache_key';
        $testValue = 'test_cache_value';

        // Test cache store and retrieve
        \Cache::put($testKey, $testValue, 60);
        $retrieved = \Cache::get($testKey);

        expect($retrieved)->toBe($testValue);

        // Clean up
        \Cache::forget($testKey);
    });
});

describe('Resource Integration', function (): void {
    it('verifies core resources are accessible', function (): void {
        $coreRoutes = [
            '/admin/products',
            '/admin/categories',
            '/admin/orders',
            '/admin/customers',
            '/admin/brands',
        ];

        foreach ($coreRoutes as $route) {
            $response = $this->actingAs($this->adminUser)->get($route);
            $response->assertStatus(200);
        }
    });

    it('verifies resource creation forms load correctly', function (): void {
        $createRoutes = [
            '/admin/products/create',
            '/admin/categories/create',
            '/admin/orders/create',
            '/admin/customers/create',
            '/admin/brands/create',
        ];

        foreach ($createRoutes as $route) {
            $response = $this->actingAs($this->adminUser)->get($route);
            $response->assertStatus(200);
        }
    });

    it('verifies admin panel navigation is complete', function (): void {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin');

        $response->assertStatus(200);

        // Check for navigation structure
        $response->assertSee('Dashboard');
    });
});

describe('Multi-language Functionality', function (): void {
    it('supports all configured languages', function (): void {
        $supportedLanguages = ['lt', 'en', 'de', 'ru'];

        foreach ($supportedLanguages as $language) {
            // Test language switching
            $response = $this->actingAs($this->adminUser)
                ->post('/admin/language/switch', ['language' => $language]);

            // Should redirect successfully
            expect($response->getStatusCode())->toBeIn([200, 302]);
        }
    });

    it('maintains session state across language switches', function (): void {
        // Switch to English
        $this->actingAs($this->adminUser)
            ->post('/admin/language/switch', ['language' => 'en']);

        // Verify admin panel still works
        $response = $this->actingAs($this->adminUser)
            ->get('/admin');

        $response->assertStatus(200);
    });
});

describe('Configuration Validation', function (): void {
    it('validates Filament configuration is properly loaded', function (): void {
        $config = config('filament');

        expect($config)->toBeArray();
        expect($config)->toHaveKey('default_filesystem_disk');
    });

    it('validates admin panel provider is registered', function (): void {
        $panel = \Filament\Facades\Filament::getPanel('admin');

        expect($panel)->not->toBeNull();
        expect($panel->getId())->toBe('admin');
        expect($panel->getPath())->toBe('/admin');
    });

    it('validates middleware configuration', function (): void {
        $panel = \Filament\Facades\Filament::getPanel('admin');
        $middleware = $panel->getMiddleware();

        // Should have essential middleware
        expect($middleware)->toContain(\Illuminate\Session\Middleware\StartSession::class);
        expect($middleware)->toContain(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    });
});
