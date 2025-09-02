<?php declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LegalSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['slug' => 'privacy', 'title' => 'Privacy Policy'],
            ['slug' => 'terms', 'title' => 'Terms of Use'],
            ['slug' => 'refund', 'title' => 'Refund Policy'],
            ['slug' => 'shipping', 'title' => 'Shipping Policy'],
        ];

        foreach ($rows as $row) {
            \App\Models\Legal::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'title' => $row['title'],
                    'content' => '<p>Demo ' . strtolower($row['title']) . ' content</p>',
                    'is_enabled' => true,
                ]
            );
        }
    }
}
