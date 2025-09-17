<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Channel;
use Illuminate\Database\Seeder;

final class ChannelSeeder extends Seeder
{
    public function run(): void
    {
        $channels = [
            [
                'name' => 'Default Store',
                'slug' => 'default',
                'url' => 'https://statybaecommerse.prus.dev',
                'is_enabled' => true,
                'is_default' => true,
            ],
        ];

        foreach ($channels as $data) {
            Channel::updateOrCreate(['slug' => $data['slug']], $data);
        }

        $this->command?->info('ChannelSeeder: default channel seeded.');
    }
}
