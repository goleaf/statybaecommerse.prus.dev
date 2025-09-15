<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SimpleReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->actingAs(User::factory()->create());
    }

    public function test_can_create_report(): void
    {
        $reportData = [
            'name' => 'Test Report',
            'slug' => 'test-report',
            'description' => 'Test report description',
            'type' => 'sales',
            'category' => 'analytics',
            'is_active' => true,
        ];

        $report = Report::create($reportData);

        $this->assertDatabaseHas('reports', [
            'name' => json_encode(['lt' => 'Test Report']),
            'slug' => 'test-report',
            'type' => 'sales',
        ]);

        $this->assertEquals('Test Report', $report->name);
        $this->assertEquals('sales', $report->type);
    }

    public function test_can_update_report(): void
    {
        $report = Report::factory()->create();

        $report->update([
            'name' => 'Updated Report Name',
            'description' => 'Updated description',
        ]);

        $this->assertEquals('Updated Report Name', $report->getTranslation('name', 'lt'));
        $this->assertEquals('Updated description', $report->getTranslation('description', 'lt'));
    }

    public function test_can_delete_report(): void
    {
        $report = Report::factory()->create();

        $report->delete();

        $this->assertSoftDeleted('reports', [
            'id' => $report->id,
        ]);
    }

    public function test_can_filter_reports_by_type(): void
    {
        Report::factory()->create(['type' => 'sales']);
        Report::factory()->create(['type' => 'inventory']);

        $salesReports = Report::where('type', 'sales')->get();
        $inventoryReports = Report::where('type', 'inventory')->get();

        $this->assertCount(1, $salesReports);
        $this->assertCount(1, $inventoryReports);
        $this->assertEquals('sales', $salesReports->first()->type);
        $this->assertEquals('inventory', $inventoryReports->first()->type);
    }

    public function test_can_filter_reports_by_status(): void
    {
        Report::factory()->create(['is_active' => true]);
        Report::factory()->create(['is_active' => false]);

        $activeReports = Report::where('is_active', true)->get();
        $inactiveReports = Report::where('is_active', false)->get();

        $this->assertCount(1, $activeReports);
        $this->assertCount(1, $inactiveReports);
        $this->assertTrue($activeReports->first()->is_active);
        $this->assertFalse($inactiveReports->first()->is_active);
    }
}
