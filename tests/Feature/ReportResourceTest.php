<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_reports(): void
    {
        $report = Report::factory()->create();

        Livewire::test(\App\Filament\Resources\ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report]);
    }

    public function test_can_create_report(): void
    {
        Livewire::test(\App\Filament\Resources\ReportResource\Pages\CreateReport::class)
            ->fillForm([
                'name' => 'Test Report',
                'slug' => 'test-report',
                'type' => 'sales',
                'category' => 'sales',
                'description' => 'Test Description',
                'content' => 'Test Content',
                'is_active' => true,
                'is_public' => false,
                'is_scheduled' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('reports', [
            'name' => 'Test Report',
            'slug' => 'test-report',
            'type' => 'sales',
            'category' => 'sales',
            'description' => 'Test Description',
            'content' => 'Test Content',
            'is_active' => true,
            'is_public' => false,
            'is_scheduled' => false,
        ]);
    }

    public function test_can_edit_report(): void
    {
        $report = Report::factory()->create();

        Livewire::test(\App\Filament\Resources\ReportResource\Pages\EditReport::class, [
            'record' => $report->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Report',
                'description' => 'Updated Description',
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $report->refresh();

        $this->assertEquals('Updated Report', $report->name);
        $this->assertEquals('Updated Description', $report->description);
        $this->assertFalse($report->is_active);
    }

    public function test_can_view_report(): void
    {
        $report = Report::factory()->create();

        Livewire::test(\App\Filament\Resources\ReportResource\Pages\ViewReport::class, [
            'record' => $report->getRouteKey(),
        ])
            ->assertCanSeeText($report->name)
            ->assertCanSeeText($report->description);
    }

    public function test_can_delete_report(): void
    {
        $report = Report::factory()->create();

        Livewire::test(\App\Filament\Resources\ReportResource\Pages\ListReports::class)
            ->callTableAction('delete', $report);

        $this->assertSoftDeleted('reports', [
            'id' => $report->id,
        ]);
    }

    public function test_can_filter_by_type(): void
    {
        $salesReport = Report::factory()->create(['type' => 'sales']);
        $inventoryReport = Report::factory()->create(['type' => 'inventory']);

        Livewire::test(\App\Filament\Resources\ReportResource\Pages\ListReports::class)
            ->filterTable('type', 'sales')
            ->assertCanSeeTableRecords([$salesReport])
            ->assertCanNotSeeTableRecords([$inventoryReport]);
    }

    public function test_can_filter_by_category(): void
    {
        $salesReport = Report::factory()->create(['category' => 'sales']);
        $marketingReport = Report::factory()->create(['category' => 'marketing']);

        Livewire::test(\App\Filament\Resources\ReportResource\Pages\ListReports::class)
            ->filterTable('category', 'sales')
            ->assertCanSeeTableRecords([$salesReport])
            ->assertCanNotSeeTableRecords([$marketingReport]);
    }

    public function test_can_filter_by_active_status(): void
    {
        $activeReport = Report::factory()->create(['is_active' => true]);
        $inactiveReport = Report::factory()->create(['is_active' => false]);

        Livewire::test(\App\Filament\Resources\ReportResource\Pages\ListReports::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$activeReport])
            ->assertCanNotSeeTableRecords([$inactiveReport]);
    }

    public function test_can_filter_by_public_status(): void
    {
        $publicReport = Report::factory()->create(['is_public' => true]);
        $privateReport = Report::factory()->create(['is_public' => false]);

        Livewire::test(\App\Filament\Resources\ReportResource\Pages\ListReports::class)
            ->filterTable('is_public', true)
            ->assertCanSeeTableRecords([$publicReport])
            ->assertCanNotSeeTableRecords([$privateReport]);
    }

    public function test_can_filter_by_scheduled_status(): void
    {
        $scheduledReport = Report::factory()->create(['is_scheduled' => true]);
        $manualReport = Report::factory()->create(['is_scheduled' => false]);

        Livewire::test(\App\Filament\Resources\ReportResource\Pages\ListReports::class)
            ->filterTable('is_scheduled', true)
            ->assertCanSeeTableRecords([$scheduledReport])
            ->assertCanNotSeeTableRecords([$manualReport]);
    }

    public function test_can_generate_report_action(): void
    {
        $report = Report::factory()->create();

        Livewire::test(\App\Filament\Resources\ReportResource\Pages\ListReports::class)
            ->callTableAction('generate', $report)
            ->assertNotified('Report generated successfully');

        $report->refresh();
        $this->assertNotNull($report->last_generated_at);
    }

    public function test_can_bulk_generate_reports(): void
    {
        $reports = Report::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\ReportResource\Pages\ListReports::class)
            ->callTableBulkAction('generate_all', $reports)
            ->assertNotified('Selected reports generated successfully');

        foreach ($reports as $report) {
            $report->refresh();
            $this->assertNotNull($report->last_generated_at);
        }
    }

    public function test_can_bulk_activate_reports(): void
    {
        $reports = Report::factory()->count(3)->create(['is_active' => false]);

        Livewire::test(\App\Filament\Resources\ReportResource\Pages\ListReports::class)
            ->callTableBulkAction('activate', $reports)
            ->assertNotified('Selected reports activated successfully');

        foreach ($reports as $report) {
            $report->refresh();
            $this->assertTrue($report->is_active);
        }
    }

    public function test_can_bulk_deactivate_reports(): void
    {
        $reports = Report::factory()->count(3)->create(['is_active' => true]);

        Livewire::test(\App\Filament\Resources\ReportResource\Pages\ListReports::class)
            ->callTableBulkAction('deactivate', $reports)
            ->assertNotified('Selected reports deactivated successfully');

        foreach ($reports as $report) {
            $report->refresh();
            $this->assertFalse($report->is_active);
        }
    }

    public function test_can_search_reports(): void
    {
        $report1 = Report::factory()->create(['name' => 'Sales Report']);
        $report2 = Report::factory()->create(['name' => 'Inventory Report']);

        Livewire::test(\App\Filament\Resources\ReportResource\Pages\ListReports::class)
            ->searchTable('Sales')
            ->assertCanSeeTableRecords([$report1])
            ->assertCanNotSeeTableRecords([$report2]);
    }

    public function test_can_sort_reports(): void
    {
        $report1 = Report::factory()->create(['name' => 'A Report']);
        $report2 = Report::factory()->create(['name' => 'B Report']);

        Livewire::test(\App\Filament\Resources\ReportResource\Pages\ListReports::class)
            ->sortTable('name')
            ->assertCanSeeTableRecords([$report1, $report2], inOrder: true);
    }

    public function test_form_validation_works(): void
    {
        Livewire::test(\App\Filament\Resources\ReportResource\Pages\CreateReport::class)
            ->fillForm([
                'name' => '',  // Required field
                'slug' => '',  // Required field
                'type' => '',  // Required field
                'category' => '',  // Required field
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'slug', 'type', 'category']);
    }

    public function test_slug_is_auto_generated_from_name(): void
    {
        Livewire::test(\App\Filament\Resources\ReportResource\Pages\CreateReport::class)
            ->fillForm([
                'name' => 'Test Report Name',
                'type' => 'sales',
                'category' => 'sales',
            ])
            ->assertFormSet('slug', 'test-report-name');
    }

    public function test_schedule_frequency_is_visible_when_scheduled(): void
    {
        Livewire::test(\App\Filament\Resources\ReportResource\Pages\CreateReport::class)
            ->fillForm([
                'name' => 'Test Report',
                'type' => 'sales',
                'category' => 'sales',
                'is_scheduled' => true,
            ])
            ->assertFormFieldExists('schedule_frequency');
    }

    public function test_relationships_are_loaded(): void
    {
        $user = User::factory()->create();
        $report = Report::factory()->create(['generated_by' => $user->id]);

        Livewire::test(\App\Filament\Resources\ReportResource\Pages\ViewReport::class, [
            'record' => $report->getRouteKey(),
        ])
            ->assertCanSeeText($user->name);
    }

    public function test_download_action_is_visible_for_generated_reports(): void
    {
        $generatedReport = Report::factory()->create(['last_generated_at' => now()]);
        $ungeneratedReport = Report::factory()->create(['last_generated_at' => null]);

        Livewire::test(\App\Filament\Resources\ReportResource\Pages\ListReports::class)
            ->assertTableActionExists('download', $generatedReport)
            ->assertTableActionDoesNotExist('download', $ungeneratedReport);
    }
}
