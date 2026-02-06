<?php

declare(strict_types=1);

use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Jobs\ProcessExport;
use App\Models\Export;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Export\Exporters\OrderExport;
use App\Services\Export\Exporters\ProductExport;
use App\Services\Export\Exporters\UserExport;
use App\Services\Export\Writers\CsvExportWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->resolveAdminPanel();

    config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
    app()->setLocale('en');

    config()->set('export.disk', 'public');
    config()->set('export.formats', [
        'csv' => CsvExportWriter::class,
    ]);

    Storage::fake('public');
    Notification::fake();
    Bus::fake();

    $this->admin = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    $this->actingAs($this->admin);
});

it('dispatches orders bulk export job with selected columns', function (): void {
    $orders = Order::factory()->count(3)->create();

    Livewire::test(ListOrders::class)
        ->callTableBulkAction('export_selected', $orders, [
            'format'  => 'csv',
            'columns' => ['number' => true, 'status' => true],
        ])
        ->assertHasNoTableBulkActionErrors();

    $export = Export::query()->latest()->first();

    expect($export)->not->toBeNull()
        ->and($export->exportable_type)->toBe(OrderExport::class)
        ->and($export->columns)->toBe(['number', 'status'])
        ->and($export->requested_by)->toBe($this->admin->getKey());

    Bus::assertDispatched(ProcessExport::class, fn (ProcessExport $job): bool => $job->exportId === $export->getKey());
});

it('uses csv format for products bulk export', function (): void {
    Product::factory()->count(2)->create();

    Livewire::test(ListProducts::class)
        ->callTableBulkAction('export_selected', Product::all(), [
            'format'  => 'csv',
            'columns' => ['sku' => true, 'name' => true],
        ])
        ->assertHasNoTableBulkActionErrors();

    $export = Export::query()->latest()->first();

    expect($export)->not->toBeNull()
        ->and($export->exportable_type)->toBe(ProductExport::class)
        ->and($export->format)->toBe('csv')
        ->and($export->columns)->toBe(['sku', 'name']);
});

it('defaults users bulk export format to csv', function (): void {
    $users = User::factory()->count(2)->create();

    Livewire::test(ListUsers::class)
        ->callTableBulkAction('export_selected', $users)
        ->assertHasNoTableBulkActionErrors();

    $export = Export::query()->latest()->first();

    expect($export)->not->toBeNull()
        ->and($export->exportable_type)->toBe(UserExport::class)
        ->and($export->format)->toBe('csv')
        ->and($export->columns)->not->toBeEmpty();
});
