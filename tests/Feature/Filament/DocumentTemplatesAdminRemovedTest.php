<?php

declare(strict_types=1);

use App\Enums\DocumentTemplateCategory;
use App\Enums\DocumentTemplateType;
use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->resolveAdminPanel();

    $this->admin = AdminUser::factory()->create([
        'email' => 'info@egisstatyba.lt',
    ]);

    $this->actingAs($this->admin, 'admin');
});

it('does not register document template admin routes', function (): void {
    expect(Route::has('filament.admin.resources.document-templates.index'))->toBeFalse()
        ->and(Route::has('filament.admin.resources.document-templates.create'))->toBeFalse()
        ->and(Route::has('filament.admin.resources.document-templates.view'))->toBeFalse()
        ->and(Route::has('filament.admin.resources.document-templates.edit'))->toBeFalse();

    $matchingPaths = collect(app('router')->getRoutes()->getRoutes())
        ->map(static fn ($route): string => (string) $route->uri())
        ->filter(static fn (string $uri): bool => str_contains($uri, 'document-templates'))
        ->values();

    expect($matchingPaths)->toBeInstanceOf(Collection::class)
        ->and($matchingPaths)->toHaveCount(0);
});

it('returns 404 for direct access to removed document templates admin url', function (): void {
    $this->get('/admin/document-templates')->assertNotFound();
});

it('keeps enum translation options for template types and categories', function (): void {
    $typeOptions = DocumentTemplateType::options();
    $categoryOptions = DocumentTemplateCategory::options();

    expect($typeOptions['invoice'] ?? null)->toBeString()->not->toBe('')
        ->and($typeOptions['document'] ?? null)->toBeString()->not->toBe('')
        ->and($categoryOptions['business'] ?? null)->toBeString()->not->toBe('')
        ->and($categoryOptions['financial'] ?? null)->toBeString()->not->toBe('');
});
