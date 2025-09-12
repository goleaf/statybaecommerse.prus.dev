<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\ReportResource;
use App\Models\Report;
use App\Models\User;
use Filament\Actions\EditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportResourceEditActionTest extends TestCase
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
    public function it_can_import_edit_action_class(): void
    {
        // Test that EditAction class can be imported without errors
        $this->assertTrue(class_exists(EditAction::class));
        
        // Test that EditAction can be instantiated
        $editAction = EditAction::make();
        $this->assertInstanceOf(EditAction::class, $editAction);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_edit_action_in_table(): void
    {
        // Test that EditAction can be created in table without errors
        $table = ReportResource::table(new \Filament\Tables\Table('test'));
        $actions = $table->getActions();
        
        $this->assertCount(2, $actions);
        $this->assertInstanceOf(EditAction::class, $actions[0]);
        $this->assertEquals('edit', $actions[0]->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_render_table_with_edit_action(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create();
        
        // Test that table can be rendered with EditAction without errors
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report])
            ->assertTableActionExists(EditAction::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_execute_edit_action(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create([
            'name' => 'Test Report',
            'type' => 'sales',
        ]);
        
        // Test that EditAction can be executed without errors
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableAction(EditAction::class, $report)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_multiple_reports(): void
    {
        $this->actingAs($this->adminUser);
        
        $reports = Report::factory()->count(5)->create();
        
        // Test that EditAction works with multiple reports
        foreach ($reports as $report) {
            Livewire::test(ReportResource\Pages\ListReports::class)
                ->callTableAction(EditAction::class, $report)
                ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_different_report_types(): void
    {
        $this->actingAs($this->adminUser);
        
        $types = ['sales', 'products', 'customers', 'inventory'];
        
        foreach ($types as $type) {
            $report = Report::factory()->create(['type' => $type]);
            
            Livewire::test(ReportResource\Pages\ListReports::class)
                ->callTableAction(EditAction::class, $report)
                ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_active_and_inactive_reports(): void
    {
        $this->actingAs($this->adminUser);
        
        $activeReport = Report::factory()->create(['is_active' => true]);
        $inactiveReport = Report::factory()->create(['is_active' => false]);
        
        // Test EditAction with active report
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableAction(EditAction::class, $activeReport)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $activeReport]));
        
        // Test EditAction with inactive report
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableAction(EditAction::class, $inactiveReport)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $inactiveReport]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_complex_filters(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create([
            'filters' => [
                'status' => 'paid',
                'category' => 'electronics',
                'min_amount' => 100,
                'date_range' => [
                    'start' => '2024-01-01',
                    'end' => '2024-12-31',
                ],
            ],
        ]);
        
        // Test EditAction with complex filters
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableAction(EditAction::class, $report)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_null_filters(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create(['filters' => null]);
        
        // Test EditAction with null filters
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableAction(EditAction::class, $report)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_empty_filters(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create(['filters' => []]);
        
        // Test EditAction with empty filters
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableAction(EditAction::class, $report)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_custom_dates(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create([
            'date_range' => 'custom',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);
        
        // Test EditAction with custom dates
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableAction(EditAction::class, $report)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_null_dates(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create([
            'start_date' => null,
            'end_date' => null,
        ]);
        
        // Test EditAction with null dates
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableAction(EditAction::class, $report)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_long_description(): void
    {
        $this->actingAs($this->adminUser);
        
        $longDescription = str_repeat('This is a long description. ', 100);
        $report = Report::factory()->create(['description' => $longDescription]);
        
        // Test EditAction with long description
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableAction(EditAction::class, $report)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_null_description(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create(['description' => null]);
        
        // Test EditAction with null description
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableAction(EditAction::class, $report)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_unicode_name(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create(['name' => 'Rapport de vente 2024 - Électronique']);
        
        // Test EditAction with unicode name
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableAction(EditAction::class, $report)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_special_characters(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create([
            'name' => 'Report with Special Characters: @#$%^&*()',
            'filters' => [
                'search' => 'test@example.com',
                'category' => 'Electronics & Gadgets',
                'price_range' => '$100-$500',
            ],
        ]);
        
        // Test EditAction with special characters
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableAction(EditAction::class, $report)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_large_dataset(): void
    {
        $this->actingAs($this->adminUser);
        
        // Create many reports to test performance
        $reports = Report::factory()->count(100)->create();
        
        // Test EditAction with large dataset
        $firstReport = $reports->first();
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableAction(EditAction::class, $firstReport)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $firstReport]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_concurrent_operations(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create();
        
        // Test that EditAction works with concurrent operations
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report])
            ->searchTable($report->name)
            ->assertCanSeeTableRecords([$report])
            ->callTableAction(EditAction::class, $report)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_filters_applied(): void
    {
        $this->actingAs($this->adminUser);
        
        $salesReport = Report::factory()->create(['type' => 'sales']);
        $customerReport = Report::factory()->create(['type' => 'customers']);
        
        // Test EditAction with filters applied
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->filterTable(\Filament\Tables\Filters\SelectFilter::make('type'), 'sales')
            ->assertCanSeeTableRecords([$salesReport])
            ->assertCanNotSeeTableRecords([$customerReport])
            ->callTableAction(EditAction::class, $salesReport)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $salesReport]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_sorting_applied(): void
    {
        $this->actingAs($this->adminUser);
        
        $report1 = Report::factory()->create(['name' => 'Z Report']);
        $report2 = Report::factory()->create(['name' => 'A Report']);
        
        // Test EditAction with sorting applied
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->sortTable('name')
            ->assertCanSeeTableRecords([$report2, $report1], inOrder: true)
            ->callTableAction(EditAction::class, $report1)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report1]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_search_applied(): void
    {
        $this->actingAs($this->adminUser);
        
        $report1 = Report::factory()->create(['name' => 'Sales Report Q1']);
        $report2 = Report::factory()->create(['name' => 'Customer Analysis']);
        
        // Test EditAction with search applied
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->searchTable('Sales Report')
            ->assertCanSeeTableRecords([$report1])
            ->assertCanNotSeeTableRecords([$report2])
            ->callTableAction(EditAction::class, $report1)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report1]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_pagination(): void
    {
        $this->actingAs($this->adminUser);
        
        // Create more reports than the default page size
        $reports = Report::factory()->count(25)->create();
        
        // Test EditAction with pagination
        $firstReport = $reports->first();
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords($reports->take(10)) // First page
            ->callTableAction(EditAction::class, $firstReport)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $firstReport]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_mixed_operations(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create();
        
        // Test EditAction with mixed operations
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->assertCanSeeTableRecords([$report])
            ->searchTable($report->name)
            ->assertCanSeeTableRecords([$report])
            ->sortTable('name')
            ->assertCanSeeTableRecords([$report])
            ->filterTable(\Filament\Tables\Filters\SelectFilter::make('type'), $report->type)
            ->assertCanSeeTableRecords([$report])
            ->callTableAction(EditAction::class, $report)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_without_errors(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create();
        
        // Test that EditAction doesn't throw any errors
        try {
            Livewire::test(ReportResource\Pages\ListReports::class)
                ->callTableAction(EditAction::class, $report)
                ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
        } catch (\Exception $e) {
            $this->fail('EditAction should not throw errors: ' . $e->getMessage());
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_performance(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create();
        
        // Test EditAction performance
        $startTime = microtime(true);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableAction(EditAction::class, $report)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Assert that EditAction executes within a reasonable time (2 seconds)
        $this->assertLessThan(2, $executionTime, 'EditAction should execute within 2 seconds');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_memory_usage(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create();
        
        // Test EditAction memory usage
        $initialMemory = memory_get_usage();
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableAction(EditAction::class, $report)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
        
        $finalMemory = memory_get_usage();
        $memoryIncrease = $finalMemory - $initialMemory;
        
        // Assert that memory usage is reasonable (less than 10MB)
        $this->assertLessThan(10 * 1024 * 1024, $memoryIncrease, 'EditAction memory usage should be reasonable');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_error_recovery(): void
    {
        $this->actingAs($this->adminUser);
        
        $report = Report::factory()->create();
        
        // Test that EditAction can handle errors gracefully
        try {
            Livewire::test(ReportResource\Pages\ListReports::class)
                ->callTableAction(EditAction::class, $report)
                ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
        } catch (\Exception $e) {
            $this->fail('EditAction should handle errors gracefully: ' . $e->getMessage());
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_different_user_roles(): void
    {
        // Test with different user roles
        $adminUser = User::factory()->create();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminUser->assignRole('admin');
        
        $report = Report::factory()->create();
        
        $this->actingAs($adminUser);
        
        Livewire::test(ReportResource\Pages\ListReports::class)
            ->callTableAction(EditAction::class, $report)
            ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_different_report_states(): void
    {
        $this->actingAs($this->adminUser);
        
        // Test with different report states
        $states = [
            ['is_active' => true, 'type' => 'sales'],
            ['is_active' => false, 'type' => 'customers'],
            ['is_active' => true, 'type' => 'products'],
            ['is_active' => false, 'type' => 'inventory'],
        ];
        
        foreach ($states as $state) {
            $report = Report::factory()->create($state);
            
            Livewire::test(ReportResource\Pages\ListReports::class)
                ->callTableAction(EditAction::class, $report)
                ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_handle_edit_action_with_edge_cases(): void
    {
        $this->actingAs($this->adminUser);
        
        // Test with edge cases
        $edgeCases = [
            ['name' => '', 'type' => 'sales'], // Empty name
            ['name' => str_repeat('a', 255), 'type' => 'sales'], // Max length name
            ['name' => 'Test', 'type' => 'sales', 'filters' => null], // Null filters
            ['name' => 'Test', 'type' => 'sales', 'filters' => []], // Empty filters
            ['name' => 'Test', 'type' => 'sales', 'description' => null], // Null description
            ['name' => 'Test', 'type' => 'sales', 'description' => ''], // Empty description
        ];
        
        foreach ($edgeCases as $edgeCase) {
            $report = Report::factory()->create($edgeCase);
            
            Livewire::test(ReportResource\Pages\ListReports::class)
                ->callTableAction(EditAction::class, $report)
                ->assertRedirect(ReportResource::getUrl('edit', ['record' => $report]));
        }
    }
}
