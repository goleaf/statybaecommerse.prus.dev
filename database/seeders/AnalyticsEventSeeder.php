<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Database\Seeder;

final class AnalyticsEventSeeder extends Seeder
{
    public function run(): void
    {
        // Create some users first
        $users = User::factory()->count(10)->create();

        // Create various types of analytics events
        $this->createPageViewEvents($users);
        $this->createProductViewEvents($users);
        $this->createPurchaseEvents($users);
        $this->createSearchEvents($users);
        $this->createFormSubmitEvents($users);
        $this->createAnonymousEvents();
    }

    private function createPageViewEvents($users): void
    {
        AnalyticsEvent::factory()
            ->count(50)
            ->pageView()
            ->withUser()
            ->recent()
            ->create();
    }

    private function createProductViewEvents($users): void
    {
        AnalyticsEvent::factory()
            ->count(30)
            ->productView()
            ->withUser()
            ->recent()
            ->create();
    }

    private function createPurchaseEvents($users): void
    {
        AnalyticsEvent::factory()
            ->count(20)
            ->purchase()
            ->withUser()
            ->recent()
            ->create();
    }

    private function createSearchEvents($users): void
    {
        AnalyticsEvent::factory()
            ->count(25)
            ->state(['event_type' => 'search'])
            ->withUser()
            ->recent()
            ->create();
    }

    private function createFormSubmitEvents($users): void
    {
        AnalyticsEvent::factory()
            ->count(15)
            ->state(['event_type' => 'form_submit'])
            ->withUser()
            ->recent()
            ->create();
    }

    private function createAnonymousEvents(): void
    {
        AnalyticsEvent::factory()
            ->count(40)
            ->anonymous()
            ->recent()
            ->create();
    }
}
