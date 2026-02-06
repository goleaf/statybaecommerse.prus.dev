<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ContactMessage;

final class ContactMessageSeeder extends BaseSeeder
{
    public function run(): void
    {
        ContactMessage::factory()
            ->count(24)
            ->create();
    }
}
