<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\ReportController;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

final class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_reports_index(): void
    {
        Report::factory()->count(5)->active()->public()->create();

        $response = app(ReportController::class)->index(Request::create('/reports', 'GET'));

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('reports.index', $response->name());
        $this->assertArrayHasKey('reports', $response->getData());
    }

    public function test_can_view_public_report(): void
    {
        $report = Report::factory()->public()->active()->create();

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
        $user = $this->createUserWithReportPermission();
        $report = Report::factory()->create(['is_public' => false]);

        $response = $this->actingAs($user)->get(route('reports.show', $report));

        $response->assertStatus(200);
        $response->assertViewIs('reports.show');
    }

    public function test_can_download_public_report(): void
    {
        $report = Report::factory()->public()->active()->create();

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
        $user = $this->createUserWithReportPermission();
        $report = Report::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reports.generate', $report));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reports', [
            'id'           => $report->id,
            'generated_by' => $user->id,
        ]);
    }

    public function test_cannot_generate_report_without_auth(): void
    {
        $report = Report::factory()->create();

        $response = $this->post(route('reports.generate', $report));

        $response->assertRedirect();
        $this->assertStringContainsString('/login', $response->headers->get('Location'));
    }

    public function test_authenticated_user_without_permission_cannot_view_private_report(): void
    {
        $user = User::factory()->create();
        $report = Report::factory()->create(['is_public' => false]);

        $response = $this->actingAs($user)->get(route('reports.show', $report));

        $response->assertStatus(403);
    }

    public function test_authenticated_user_without_permission_cannot_generate_report(): void
    {
        $user = User::factory()->create();
        $report = Report::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reports.generate', $report));

        $response->assertStatus(403);
    }

    public function test_reports_index_filters_by_type(): void
    {
        Report::factory()->active()->public()->create(['type' => 'sales']);
        Report::factory()->active()->public()->create(['type' => 'products']);

        $response = app(ReportController::class)->index(Request::create('/reports', 'GET', ['type' => 'sales']));

        $this->assertInstanceOf(View::class, $response);
        $reports = $response->getData()['reports'];
        $this->assertCount(1, $reports);
    }

    public function test_reports_index_filters_by_category(): void
    {
        Report::factory()->active()->public()->create(['category' => 'sales']);
        Report::factory()->active()->public()->create(['category' => 'marketing']);

        $response = app(ReportController::class)->index(Request::create('/reports', 'GET', ['report_category' => 'sales']));

        $this->assertInstanceOf(View::class, $response);
        $reports = $response->getData()['reports'];
        $this->assertCount(1, $reports);
    }

    public function test_reports_index_redirects_legacy_category_parameter(): void
    {
        Report::factory()->active()->public()->create(['category' => 'sales']);

        $response = app(ReportController::class)->index(Request::create('/reports', 'GET', ['category' => 'sales']));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('reports.index', ['report_category' => 'sales']), $response->getTargetUrl());
    }

    public function test_reports_index_searches_by_name(): void
    {
        app()->setLocale('en');

        Report::factory()->active()->public()->create([
            'name' => ['lt' => 'Pardavimų ataskaita', 'en' => 'Sales Report'],
        ]);
        Report::factory()->active()->public()->create([
            'name' => ['lt' => 'Produktų ataskaita', 'en' => 'Product Report'],
        ]);

        $response = app(ReportController::class)->index(Request::create('/reports', 'GET', ['search' => 'Sales']));

        $this->assertInstanceOf(View::class, $response);
        $reports = $response->getData()['reports'];
        $this->assertCount(1, $reports);
    }

    public function test_view_count_increments_when_viewing_report(): void
    {
        $report = Report::factory()->public()->active()->create(['view_count' => 0]);

        $this->get(route('reports.show', $report));

        $report->refresh();
        $this->assertEquals(1, $report->view_count);
    }

    public function test_download_count_increments_when_downloading_report(): void
    {
        $report = Report::factory()->public()->active()->create(['download_count' => 0]);

        $this->get(route('reports.download', $report));

        $report->refresh();
        $this->assertEquals(1, $report->download_count);
    }

    public function test_download_returns_pdf_payload(): void
    {
        $report = Report::factory()->public()->active()->create();

        $response = $this->get(route('reports.download', $report));

        $response->assertOk();
        $this->assertTrue(str_starts_with($response->getContent(), '%PDF'));
    }

    public function test_index_rejects_invalid_sorting_payload(): void
    {
        $this->expectException(ValidationException::class);

        app(ReportController::class)->index(Request::create('/reports', 'GET', [
            'sort'      => 'invalid',
            'direction' => 'sideways',
        ]));
    }

    /**
     * Create a user primed with the view_reports permission for private access flows.
     */
    private function createUserWithReportPermission(): User
    {
        // Ensure the permission cache is flushed before attaching new permissions.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Lazily create the permission so tests remain isolated from seeders.
        Permission::findOrCreate('view_reports');

        $user = User::factory()->create();
        $user->givePermissionTo('view_reports');

        return $user;
    }
}
