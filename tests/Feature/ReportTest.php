<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_reports_index(): void
    {
        Report::factory()->count(5)->create();

        $response = $this->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
        $response->assertViewHas('reports');
    }

    public function test_can_view_public_report(): void
    {
        $report = Report::factory()->public()->create();

        $response = $this->get(route('reports.show', $report));

        $response->assertStatus(200);
        $response->assertViewIs('reports.show');
        $response->assertViewHas('report', $report);
    }

    public function test_cannot_view_private_report_without_auth(): void
    {
        $report = Report::factory()->create(['is_public' => false]);

        $response = $this->get(route('reports.show', $report));

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_view_private_report(): void
    {
        $user = User::factory()->create();
        $report = Report::factory()->create(['is_public' => false]);

        $response = $this->actingAs($user)->get(route('reports.show', $report));

        $response->assertStatus(200);
        $response->assertViewIs('reports.show');
    }

    public function test_can_download_public_report(): void
    {
        $report = Report::factory()->public()->create();

        $response = $this->get(route('reports.download', $report));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_cannot_download_private_report_without_auth(): void
    {
        $report = Report::factory()->create(['is_public' => false]);

        $response = $this->get(route('reports.download', $report));

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_generate_report(): void
    {
        $user = User::factory()->create();
        $report = Report::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reports.generate', $report));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'generated_by' => $user->id,
        ]);
    }

    public function test_cannot_generate_report_without_auth(): void
    {
        $report = Report::factory()->create();

        $response = $this->post(route('reports.generate', $report));

        $response->assertStatus(403);
    }

    public function test_reports_index_filters_by_type(): void
    {
        Report::factory()->create(['type' => 'sales']);
        Report::factory()->create(['type' => 'products']);

        $response = $this->get(route('reports.index', ['type' => 'sales']));

        $response->assertStatus(200);
        $reports = $response->viewData('reports');
        $this->assertCount(1, $reports);
    }

    public function test_reports_index_filters_by_category(): void
    {
        Report::factory()->create(['category' => 'sales']);
        Report::factory()->create(['category' => 'marketing']);

        $response = $this->get(route('reports.index', ['category' => 'sales']));

        $response->assertStatus(200);
        $reports = $response->viewData('reports');
        $this->assertCount(1, $reports);
    }

    public function test_reports_index_searches_by_name(): void
    {
        Report::factory()->create([
            'name' => ['lt' => 'Pardavimų ataskaita', 'en' => 'Sales Report']
        ]);
        Report::factory()->create([
            'name' => ['lt' => 'Produktų ataskaita', 'en' => 'Product Report']
        ]);

        $response = $this->get(route('reports.index', ['search' => 'Pardavimų']));

        $response->assertStatus(200);
        $reports = $response->viewData('reports');
        $this->assertCount(1, $reports);
    }

    public function test_view_count_increments_when_viewing_report(): void
    {
        $report = Report::factory()->public()->create(['view_count' => 0]);

        $this->get(route('reports.show', $report));

        $report->refresh();
        $this->assertEquals(1, $report->view_count);
    }

    public function test_download_count_increments_when_downloading_report(): void
    {
        $report = Report::factory()->public()->create(['download_count' => 0]);

        $this->get(route('reports.download', $report));

        $report->refresh();
        $this->assertEquals(1, $report->download_count);
    }
}
