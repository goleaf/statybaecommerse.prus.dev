<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class TestAnalyticsSimpleCommand extends Command
{
    protected $signature = 'app:test-analytics-simple';

    protected $description = 'Run a simple smoke test for the AnalyticsEvent model.';

    public function handle(): int
    {
        $this->info('Testing AnalyticsEvent model...');

        try {
            $user = User::create([
                'name' => 'Test User',
                'email' => Str::uuid().'@example.com',
                'password' => bcrypt('password'),
            ]);

            $this->line('User created successfully');

            $event = new AnalyticsEvent;
            $event->event_name = 'Test Event';
            $event->event_type = 'page_view';
            $event->user_id = $user->id;
            $event->session_id = 'test-session-'.Str::uuid();
            $event->save();

            $this->line('AnalyticsEvent created successfully');
            $this->line('Event ID: '.$event->id);
            $this->line('Event Name: '.$event->event_name);
            $this->line('Event Type: '.$event->event_type);
            $this->line('User from event: '.$event->user->name);

            $this->info('All tests passed!');
        } catch (\Throwable $exception) {
            $this->error('Error: '.$exception->getMessage());
            $this->error('File: '.$exception->getFile());
            $this->error('Line: '.$exception->getLine());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
