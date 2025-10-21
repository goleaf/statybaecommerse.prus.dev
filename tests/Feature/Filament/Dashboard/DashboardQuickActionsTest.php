<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Dashboard;

use App\Filament\Widgets\DashboardQuickActionsWidget;
use App\Jobs\ClearApplicationCacheJob;
use App\Jobs\RebuildSearchIndexJob;
use App\Jobs\RunMinimalSeedJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use Tests\Feature\TestCase;

final class DashboardQuickActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_quick_actions_dispatch_expected_jobs(): void
    {
        $user = User::factory()->admin()->create();

        Livewire::actingAs($user);

        Bus::fake();

        Livewire::test(DashboardQuickActionsWidget::class)
            ->callAction('rebuildSearchIndex')
            ->callAction('clearCache')
            ->callAction('runMinimalSeed');

        Bus::assertDispatched(RebuildSearchIndexJob::class);
        Bus::assertDispatched(ClearApplicationCacheJob::class);
        Bus::assertDispatched(RunMinimalSeedJob::class);
    }
}
