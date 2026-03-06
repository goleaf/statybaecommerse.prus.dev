<?php

declare(strict_types=1);

use App\Models\Supplier;
use Database\Seeders\SupplierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds suppliers with company and contact fields', function (): void {
    $this->seed(SupplierSeeder::class);

    $suppliers = Supplier::query()
        ->orderBy('company_code')
        ->get();

    expect($suppliers)->toHaveCount(3);

    foreach ($suppliers as $supplier) {
        expect($supplier->company_code)->not->toBe('')
            ->and($supplier->code)->toMatch('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
            ->and($supplier->contact_person)->not->toBeNull()
            ->and($supplier->contact_email)->not->toBeNull();
    }
});

it('is idempotent when supplier seeder is run multiple times', function (): void {
    $this->seed(SupplierSeeder::class);
    $this->seed(SupplierSeeder::class);

    expect(Supplier::query()->count())->toBe(3);
});
