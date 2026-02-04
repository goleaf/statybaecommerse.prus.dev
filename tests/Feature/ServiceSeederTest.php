<?php

declare(strict_types=1);

use App\Models\Service;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds 20 services', function (): void {
    expect(Service::count())->toBe(0);

    $seeder = new ServiceSeeder;
    $seeder->run();

    expect(Service::count())->toBe(20);
});
