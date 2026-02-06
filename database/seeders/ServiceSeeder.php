<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Service;

class ServiceSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Service::factory()->count(20)->create();
    }
}
