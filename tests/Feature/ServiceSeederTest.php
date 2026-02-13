<?php

declare(strict_types=1);

use App\Models\Service;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds curated lithuanian services with all required fields', function (): void {
    expect(Service::count())->toBe(0);

    $seeder = new ServiceSeeder;
    $seeder->run();

    expect(Service::count())->toBe(20);
    expect(
        Service::query()
            ->whereNull('name')
            ->orWhere('name', '')
            ->orWhereNull('description')
            ->orWhere('description', '')
            ->orWhere('price', '<=', 0)
            ->count()
    )->toBe(0);

    expect(
        Service::query()->where('name', 'Statybinių medžiagų pristatymas į objektą')->exists()
    )->toBeTrue();

    expect(
        Service::query()->where('name', 'like', '%ų%')->orWhere('name', 'like', '%ė%')->exists()
    )->toBeTrue();
});

it('is idempotent and keeps service field values up to date', function (): void {
    $seeder = new ServiceSeeder;
    $seeder->run();

    $service = Service::query()->where('name', 'Statybinių medžiagų pristatymas į objektą')->firstOrFail();
    $service->update([
        'description' => 'Laikina reikšmė testui',
        'price'       => 1,
        'is_active'   => false,
    ]);

    $seeder->run();

    $service->refresh();

    expect(Service::count())->toBe(20);
    expect($service->description)->toBe('Pristatome statybines medžiagas tiesiai į objektą visoje Lietuvoje, suderinę laiką su jūsų darbų grafiku.');
    expect((float) $service->price)->toBe(89.0);
    expect($service->is_active)->toBeTrue();
});

it('registers the service seeder in standard seed profiles', function (): void {
    $standardSeeders = config('seeds.standard_seeders', []);

    expect($standardSeeders)->toContain(ServiceSeeder::class);
});
