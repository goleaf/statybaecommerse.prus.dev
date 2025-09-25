<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Channel;
use Illuminate\Database\Seeder;

final class ChannelSeeder extends Seeder
{
    public function run(): void
    {
        // Check if default channel already exists to maintain idempotency
        $existingChannel = Channel::where('slug', 'default')->first();
        
        if (!$existingChannel) {
            // Create default channel using factory with specific attributes
            Channel::factory()
                ->state([
                    'name' => 'Default Store',
                    'slug' => 'default',
                    'url' => 'https://statybaecommerse.prus.dev',
                    'is_enabled' => true,
                    'is_default' => true,
                ])
                ->create();
        }

        $this->command?->info('ChannelSeeder: default channel seeded.');
    }
}
