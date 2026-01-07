<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

final class ClearNotifications extends Command
{
    protected $signature = 'notifications:clear';

    protected $description = 'Clear all notifications from the database';

    public function handle(): int
    {
        $count = DatabaseNotification::count();

        if ($count === 0) {
            $this->info('No notifications to clear.');

            return 0;
        }

        DatabaseNotification::truncate();

        $this->info("Cleared {$count} notifications from the database.");

        return 0;
    }
}
