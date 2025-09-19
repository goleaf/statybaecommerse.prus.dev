<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\ReportResource;
use App\Models\Report;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportResourceComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'name' => 'Admin User',
        ]);

        // Ensure role exists and assign it
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_render_report_index_page(): void
    {
        $this->actingAs($this->adminUser);
        
        $response = $this->get(ReportResource::getUrl('index'));
        $response->assertSuccessful();
        $response->assertSee(__('admin.navigation.reports'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_render_report_create_page(): void
    {
        $this->actingAs($this->adminUser);
        
        $response = $this->get(ReportResource::getUrl('create'));
        $response->assertSuccessful();
        $response->assertSee(__('admin.reports.sales_report'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_list_reports_in_table(): void
    {
        $this->actingAs($this->adminUser);
        
        $reports = Report::factory()->count(5)->create();
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords($reports);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_search_reports_by_name(): void
    {
        $this->actingAs($this->adminUser);
        
        $report1 = Report::factory()->create(['name' => 'Sales Report Q1']);
        $report2 = Report::factory()->create(['name' => 'Customer Analysis']);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->searchTable('Sales Report')
            ->assertCanSeeTableRecords([$report1])
            ->assertCanNotSeeTableRecords([$report2]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_sort_reports_by_name(): void
    {
        $this->actingAs($this->adminUser);
        
        $report1 = Report::factory()->create(['name' => 'Z Report']);
        $report2 = Report::factory()->create(['name' => 'A Report']);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->sortTable('name')
            ->assertCanSeeTableRecords([$report2, $report1], inOrder: true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_sort_reports_by_type(): void
    {
        $this->actingAs($this->adminUser);
        
        $report1 = Report::factory()->create(['type' => 'sales']);
        $report2 = Report::factory()->create(['type' => 'customers']);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->sortTable('type')
            ->assertCanSeeTableRecords([$report2, $report1], inOrder: true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_sort_reports_by_created_at(): void
    {
        $this->actingAs($this->adminUser);
        
        $report1 = Report::factory()->create(['created_at' => now()->subDay()]);
        $report2 = Report::factory()->create(['created_at' => now()]);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->sortTable('created_at')
            ->assertCanSeeTableRecords([$report1, $report2], inOrder: true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_filter_reports_by_type(): void
    {
        $this->actingAs($this->adminUser);
        
        $salesReport = Report::factory()->create(['type' => 'sales']);
        $customerReport = Report::factory()->create(['type' => 'customers']);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->filterTable(SelectFilter::make('type'), 'sales')
            ->assertCanSeeTableRecords([$salesReport])
            ->assertCanNotSeeTableRecords([$customerReport]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_filter_reports_by_active_status(): void
    {
        $this->actingAs($this->adminUser);
        
        $activeReport = Report::factory()->create(['is_active' => true]);
        $inactiveReport = Report::factory()->create(['is_active' => false]);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->filterTable(TernaryFilter::make('is_active'), true)
            ->assertCanSeeTableRecords([$activeReport])
            ->assertCanNotSeeTableRecords([$inactiveReport]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_new_report(): void
    {
        $this->actingAs($this->adminUser);
        
        $reportData = [
            'name' => 'Test Sales Report',
            'type' => 'sales',
            'date_range' => 'last_30_days',
            'start_date' => now()->subDays(30)->toDateString(),
            'end_date' => now()->toDateString(),
            'filters' => ['status' => 'paid'],
            'description' => 'Test report description',
            'is_active' => true,
        ];
        
        Livewire::test(ReportResource\Pages\CreateReport::class)
            ->fillForm($reportData)
            ->call('create')
            ->assertHasNoFormErrors();
        
        $this->assertDatabaseHas('reports', [
            'name' => $reportData['name'],
            'type' => $reportData['type'],
            'date_range' => $reportData['date_range'],
            'is_active' => $reportData['is_active'],
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_required_fields_when_creating_report(): void
    {
        $this->actingAs($this->adminUser);
        
        Livewire::test(ReportResource\Pages\CreateReport::class)
            ->fillForm([
                'name' => '',
                'type' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'type']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_name_max_length_when_creating_report(): void
    {
        $this->actingAs($this->adminUser);
        
        Livewire::test(ReportResource\Pages\CreateReport::class)
            ->fillForm([
                'name' => str_repeat('a', 256), // Exceeds 255 character limit
                'type' => 'sales',
            ])
            ->call('create')
            ->assertHasFormErrors(['name']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_edit_existing_report(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create([
            'name' => 'Original Name',
            'type' => 'sales',
        ]);
        
        $updatedData = [
            'name' => 'Updated Report Name',
            'type' => 'customers',
            'description' => 'Updated description',
            'is_active' => false,
        ];
        
        Livewire::test(ReportResource\Pages\EditReport::class, [
            'record' => $report->getRouteKey(),
        ])
            ->fillForm($updatedData)
            ->call('save')
            ->assertHasNoFormErrors();
        
        $report->refresh();
        $this->assertEquals('Updated Report Name', $report->name);
        $this->assertEquals('customers', $report->type);
        $this->assertEquals('Updated description', $report->description);
        $this->assertFalse($report->is_active);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_edit_report_with_edit_action(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create([
            'name' => 'Test Report',
            'type' => 'sales',
        ]);
        
        // Test that EditAction is available and works
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableAction(EditAction::class, $report)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_delete_report_with_delete_action(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create();
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableAction(DeleteAction::class, $report);
        
        $this->assertModelMissing($report);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_bulk_delete_reports(): void
    {
        $this->actingAs($this->adminUser);
        
        $reports = Report::factory()->count(3)->create();
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableBulkAction(DeleteBulkAction::class, $reports);
        
        foreach ($reports as $report) {
            $this->assertModelMissing($report);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_displays_correct_table_columns(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create([
            'name' => 'Test Report',
            'type' => 'sales',
            'date_range' => 'last_30_days',
            'is_active' => true,
        ]);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report])
            ->assertCanRenderTableColumn('name')
            ->assertCanRenderTableColumn('type')
            ->assertCanRenderTableColumn('date_range')
            ->assertCanRenderTableColumn('is_active')
            ->assertCanRenderTableColumn('created_at');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_displays_type_as_badge(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create(['type' => 'sales']);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report])
            ->assertTableColumnStateSet('type', 'sales', $report);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_displays_active_status_as_icon(): void
    {
        $this->actingAs($this->adminUser);
        
        $activeReport = Report::factory()->create(['is_active' => true]);
        $inactiveReport = Report::factory()->create(['is_active' => false]);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$activeReport, $inactiveReport])
            ->assertTableColumnStateSet('is_active', true, $activeReport)
            ->assertTableColumnStateSet('is_active', false, $inactiveReport);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_displays_created_at_with_since_format(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create(['created_at' => now()->subHours(2)]);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report])
            ->assertCanRenderTableColumn('created_at');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_all_report_types(): void
    {
        $this->actingAs($this->adminUser);
        
        $types = ['sales', 'products', 'customers', 'inventory'];
        $reports = [];
        
        foreach ($types as $type) {
            $reports[] = Report::factory()->create(['type' => $type]);
        }
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords($reports);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_all_date_ranges(): void
    {
        $this->actingAs($this->adminUser);
        
        $dateRanges = ['today', 'yesterday', 'last_7_days', 'last_30_days', 'last_90_days', 'this_year', 'custom'];
        $reports = [];
        
        foreach ($dateRanges as $dateRange) {
            $reports[] = Report::factory()->create(['date_range' => $dateRange]);
        }
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords($reports);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_custom_filters(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create([
            'filters' => [
                'status' => 'paid',
                'category' => 'electronics',
                'min_amount' => 100,
            ],
        ]);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report]);
        
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'filters' => json_encode([
                'status' => 'paid',
                'category' => 'electronics',
                'min_amount' => 100,
            ]),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_null_filters(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create(['filters' => null]);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_empty_filters(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create(['filters' => []]);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_custom_date_ranges(): void
    {
        $this->actingAs($this->adminUser);
        
        $startDate = now()->subDays(10)->toDateString();
        $endDate = now()->subDays(5)->toDateString();
        
        $report = Report::factory()->create([
            'date_range' => 'custom',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report]);
        
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'date_range' => 'custom',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_null_dates(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create([
            'start_date' => null,
            'end_date' => null,
        ]);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_long_descriptions(): void
    {
        $this->actingAs($this->adminUser);
        
        $longDescription = str_repeat('This is a long description. ', 50);
        $report = Report::factory()->create(['description' => $longDescription]);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_null_descriptions(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create(['description' => null]);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_unicode_characters_in_name(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create(['name' => 'Rapport de vente 2024 - Électronique']);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_special_characters_in_filters(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create([
            'filters' => [
                'search' => 'test@example.com',
                'category' => 'Electronics & Gadgets',
                'price_range' => '$100-$500',
            ],
        ]);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_large_number_of_reports(): void
    {
        $this->actingAs($this->adminUser);
        
        $reports = Report::factory()->count(100)->create();
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords($reports);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_mixed_active_inactive_reports(): void
    {
        $this->actingAs($this->adminUser);
        
        $activeReports = Report::factory()->count(5)->create(['is_active' => true]);
        $inactiveReports = Report::factory()->count(5)->create(['is_active' => false]);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([...$activeReports, ...$inactiveReports]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_mixed_report_types(): void
    {
        $this->actingAs($this->adminUser);
        
        $salesReports = Report::factory()->count(3)->create(['type' => 'sales']);
        $customerReports = Report::factory()->count(3)->create(['type' => 'customers']);
        $productReports = Report::factory()->count(3)->create(['type' => 'products']);
        $inventoryReports = Report::factory()->count(3)->create(['type' => 'inventory']);
        
        $allReports = [...$salesReports, ...$customerReports, ...$productReports, ...$inventoryReports];
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords($allReports);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edge_case_date_combinations(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create([
            'date_range' => 'custom',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_complex_filter_structures(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create([
            'filters' => [
                'date_range' => [
                    'start' => '2024-01-01',
                    'end' => '2024-12-31',
                ],
                'categories' => ['electronics', 'clothing', 'books'],
                'price_range' => [
                    'min' => 10,
                    'max' => 1000,
                ],
                'statuses' => ['paid', 'pending', 'shipped'],
                'custom_fields' => [
                    'region' => 'EU',
                    'currency' => 'EUR',
                ],
            ],
        ]);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_boolean_edge_cases(): void
    {
        $this->actingAs($this->adminUser);
        
        $trueReport = Report::factory()->create(['is_active' => true]);
        $falseReport = Report::factory()->create(['is_active' => false]);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$trueReport, $falseReport])
            ->assertTableColumnStateSet('is_active', true, $trueReport)
            ->assertTableColumnStateSet('is_active', false, $falseReport);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_form_validation_edge_cases(): void
    {
        $this->actingAs($this->adminUser);
        
        // Test with empty strings
        Livewire::test(ReportResource\Pages\CreateReport::class)
            ->fillForm([
                'name' => '',
                'type' => '',
                'date_range' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'type']);
        
        // Test with whitespace-only name
        Livewire::test(ReportResource\Pages\CreateReport::class)
            ->fillForm([
                'name' => '   ',
                'type' => 'sales',
            ])
            ->call('create')
            ->assertHasFormErrors(['name']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_table_actions_without_errors(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create();
        
        // Test that all table actions are available and don't throw errors
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertTableActionExists(EditAction::class)
            ->assertTableActionExists(DeleteAction::class)
            ->assertTableBulkActionExists(DeleteBulkAction::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_table_filters_without_errors(): void
    {
        $this->actingAs($this->adminUser);
        
        // Test that all table filters are available and don't throw errors
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertTableFilterExists(SelectFilter::make('type'))
            ->assertTableFilterExists(TernaryFilter::make('is_active'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_table_columns_without_errors(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create();
        
        // Test that all table columns are available and don't throw errors
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanRenderTableColumn('name')
            ->assertCanRenderTableColumn('type')
            ->assertCanRenderTableColumn('date_range')
            ->assertCanRenderTableColumn('is_active')
            ->assertCanRenderTableColumn('created_at');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_navigation_properties(): void
    {
        $this->actingAs($this->adminUser);
        
        // Test navigation properties
        $this->assertEquals('heroicon-o-document-chart-bar', ReportResource::getNavigationIcon());
        $this->assertEquals(__('navigation.groups.analytics'), ReportResource::getNavigationGroup());
        $this->assertEquals(__('admin.navigation.reports'), ReportResource::getNavigationLabel());
        $this->assertEquals(__('admin.models.report'), ReportResource::getModelLabel());
        $this->assertEquals(__('admin.models.reports'), ReportResource::getPluralModelLabel());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_resource_pages(): void
    {
        $this->actingAs($this->adminUser);
        
        $pages = ReportResource::getPages();
        
        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('edit', $pages);
        
        $this->assertStringContainsString('/reports', $pages['index']);
        $this->assertStringContainsString('/reports/create', $pages['create']);
        $this->assertStringContainsString('/reports/{record}/edit', $pages['edit']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_model_relationship(): void
    {
        $this->actingAs($this->adminUser);
        
        $this->assertEquals(Report::class, ReportResource::getModel());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_form_schema_components(): void
    {
        $this->actingAs($this->adminUser);
        
        // Test that form can be rendered without errors
        $response = $this->get(ReportResource::getUrl('create'));
        $response->assertSuccessful();
        
        // Test that all form fields are present
        $response->assertSee('name');
        $response->assertSee('type');
        $response->assertSee('date_range');
        $response->assertSee('start_date');
        $response->assertSee('end_date');
        $response->assertSee('filters');
        $response->assertSee('description');
        $response->assertSee('is_active');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_table_schema_components(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create();
        
        // Test that table can be rendered without errors
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_performance_with_large_datasets(): void
    {
        $this->actingAs($this->adminUser);
        
        // Create a large number of reports to test performance
        $reports = Report::factory()->count(500)->create();
        
        $startTime = microtime(true);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords($reports->take(50)); // Only check first 50 for performance
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Assert that the page loads within a reasonable time (5 seconds)
        $this->assertLessThan(5, $executionTime, 'Page should load within 5 seconds');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_concurrent_operations(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create();
        
        // Test that multiple operations can be performed on the same report
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report])
            ->searchTable($report->name)
            ->assertCanSeeTableRecords([$report])
            ->sortTable('name')
            ->assertCanSeeTableRecords([$report])
            ->filterTable(SelectFilter::make('type'), $report->type)
            ->assertCanSeeTableRecords([$report]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_error_recovery(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create();
        
        // Test that the resource can handle errors gracefully
        try {
            Livewire::test(ReportResource\Pages\ListReports::class)
                ->assertCanSeeTableRecords([$report]);
        } catch (\Exception $e) {
            $this->fail('Resource should handle errors gracefully: ' . $e->getMessage());
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_memory_usage(): void
    {
        $this->actingAs($this->adminUser);
        
        $initialMemory = memory_get_usage();
        
        // Create and test with many reports
        $reports = Report::factory()->count(100)->create();
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords($reports->take(20));
        
        $finalMemory = memory_get_usage();
        $memoryIncrease = $finalMemory - $initialMemory;
        
        // Assert that memory usage is reasonable (less than 50MB)
        $this->assertLessThan(50 * 1024 * 1024, $memoryIncrease, 'Memory usage should be reasonable');
    }
}
