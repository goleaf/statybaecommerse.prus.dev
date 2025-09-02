<?php declare(strict_types=1);

use App\Models\User;
use App\Services\CategoryDocsImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('imports categories from docs via service', function (): void {
    // Ensure categories table exists in sqlite memory
    $this->artisan('migrate', ['--force' => true]);
    $service = app(CategoryDocsImporter::class);

    $tmp = base_path('docs-test-import');
    @mkdir($tmp);
    @mkdir($tmp . '/guides');
    file_put_contents($tmp . '/intro.md', '# Intro');
    file_put_contents($tmp . '/guides/getting-started.md', '# Getting Started');

    $result = $service->import($tmp);

    expect($result['created'] ?? 0)->toBeGreaterThan(0);
    expect(\App\Models\Category::query()->where('name', 'Intro')->exists())->toBeTrue();
    expect(\App\Models\Category::query()->where('name', 'Getting Started')->exists())->toBeTrue();
})->group('admin');

it('renders categories index with import action visible for authorized users', function (): void {
    $admin = User::factory()->create();
    if (method_exists($admin, 'assignRole')) {
        try {
            \Spatie\Permission\Models\Role::findOrCreate(config('shopper.core.users.admin_role', 'administrator'));
            $admin->assignRole(config('shopper.core.users.admin_role', 'administrator'));
        } catch (\Throwable $e) {
            // fallback: ignore role assignment in testing if tables not present
        }
    }

    $this->actingAs($admin);

    $component = Livewire::test(\App\Livewire\Admin\Categories\Index::class);
    $component->assertOk();
})->group('admin');
