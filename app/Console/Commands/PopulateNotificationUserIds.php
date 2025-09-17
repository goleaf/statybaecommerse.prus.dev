<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class PopulateNotificationUserIds extends Command
{
    protected $signature = 'notifications:populate-user-ids';

    protected $description = 'Populate user_id column in notifications table from notifiable_id';

    public function handle(): int
    {
        $this->info('Populating user_id column in notifications table...');

        $updated = DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->whereNull('user_id')
            ->update([
                'user_id' => DB::raw('notifiable_id'),
            ]);

        $this->info("Updated {$updated} notifications with user_id.");

        return self::SUCCESS;
    }
}
