<?php declare(strict_types=1);

namespace Tests\Feature\Filament\Pages;

use App\Filament\Pages\CustomerSegmentation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CustomerSegmentationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create(['is_admin' => true]);
        $this->actingAs($this->adminUser);
    }

    public function test_page_mounts(): void
    {
        Livewire::test(CustomerSegmentation::class)->assertOk();
    }

    public function test_table_renders_and_basic_actions_exist(): void
    {
        $c1 = User::factory()->create(['is_admin' => false]);
        $c2 = User::factory()->create(['is_admin' => false]);

        Livewire::test(CustomerSegmentation::class)
            ->assertCanSeeTableRecords([$c1, $c2])
            ->assertTableActionExists('assign_to_group', null, $c1)
            ->assertTableActionExists('send_marketing_email', null, $c1)
            ->assertTableBulkActionExists('bulk_assign_group')
            ->assertTableBulkActionExists('bulk_marketing_email');
    }

    public function test_header_action_segment_analysis_runs(): void
    {
        Livewire::test(CustomerSegmentation::class)
            ->callAction('segment_analysis');

        $this->assertTrue(true); // basic smoke to ensure no exception
    }

    public function test_can_create_segment_via_header_action(): void
    {
        Livewire::test(CustomerSegmentation::class)
            ->callAction('create_segment', data: [
                'name' => 'High Spenders',
                'description' => 'Customers who spent >= 500',
                'criteria_type' => 'total_spent',
                'criteria_value' => 500,
                'criteria_operator' => 'gte',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('customer_groups', [
            'name' => 'High Spenders',
            'is_enabled' => 1,
        ]);
    }

    public function test_can_assign_customer_to_group_via_record_action(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $group = \App\Models\CustomerGroup::factory()->create();

        Livewire::test(CustomerSegmentation::class)
            ->callTableAction('assign_to_group', $customer, [
                'customer_group_id' => $group->id,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('customer_group_user', [
            'user_id' => $customer->id,
            'customer_group_id' => $group->id,
        ]);
    }
}

