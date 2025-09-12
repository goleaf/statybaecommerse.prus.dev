<?php declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LegalSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['key' => 'privacy', 'title' => 'Privacy Policy'],
            ['key' => 'terms', 'title' => 'Terms of Use'],
            ['key' => 'refund', 'title' => 'Refund Policy'],
            ['key' => 'shipping', 'title' => 'Shipping Policy'],
        ];

        foreach ($rows as $row) {
            \App\Models\Legal::query()->updateOrCreate(
                ['key' => $row['key']],
                [
                    'is_enabled' => true,
                ]
            );
        }
    }
}
