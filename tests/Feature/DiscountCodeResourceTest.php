<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\DiscountCodeResource;
use App\Models\CustomerGroup;
use App\Models\DiscountCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class DiscountCodeResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create();
        $this->actingAs($this->adminUser);
    }

    public function test_can_list_discount_codes(): void
    {
        $customerGroup = CustomerGroup::factory()->create();

        $codes = DiscountCode::factory()->count(3)->create([
            'customer_group_id' => $customerGroup->id,
        ]);

        Livewire::test(DiscountCodeResource\Pages\ListDiscountCodes::class)
            ->assertCanSeeTableRecords($codes);
    }

    public function test_can_create_discount_code(): void
    {
        $customerGroup = CustomerGroup::factory()->create();

        Livewire::test(DiscountCodeResource\Pages\CreateDiscountCode::class)
            ->fillForm([
                'code' => 'TESTCODE',
                'name' => 'Test Discount Code',
                'description' => 'Test description',
                'type' => 'percentage',
                'value' => 10.0,
                'minimum_amount' => 50.0,
                'maximum_discount' => 100.0,
                'usage_limit' => 100,
                'usage_limit_per_user' => 1,
                'valid_from' => now(),
                'valid_until' => now()->addMonth(),
                'customer_group_id' => $customerGroup->id,
                'is_active' => true,
                'is_public' => false,
                'is_auto_apply' => false,
                'is_stackable' => false,
                'is_first_time_only' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('discount_codes', [
            'code' => 'TESTCODE',
            'name' => 'Test Discount Code',
            'type' => 'percentage',
            'value' => 10.0,
        ]);
    }

    public function test_can_edit_discount_code(): void
    {
        $customerGroup = CustomerGroup::factory()->create();

        $code = DiscountCode::factory()->create([
            'code' => 'EDITME',
            'name' => 'Editable Code',
            'type' => 'percentage',
            'value' => 15.0,
            'customer_group_id' => $customerGroup->id,
        ]);

        Livewire::test(DiscountCodeResource\Pages\EditDiscountCode::class, [
            'record' => $code->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Code Name',
                'value' => 20.0,
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('discount_codes', [
            'id' => $code->id,
            'name' => 'Updated Code Name',
            'value' => 20.0,
            'is_active' => false,
        ]);
    }

    public function test_can_delete_discount_code(): void
    {
        $customerGroup = CustomerGroup::factory()->create();

        $code = DiscountCode::factory()->create([
            'code' => 'DELME',
            'name' => 'Deletable Code',
            'type' => 'fixed',
            'value' => 5.0,
            'customer_group_id' => $customerGroup->id,
        ]);

        Livewire::test(DiscountCodeResource\Pages\EditDiscountCode::class, [
            'record' => $code->getRouteKey(),
        ])
            ->callAction('delete');

        $this->assertModelMissing($code);
    }

    public function test_discount_code_has_correct_relationships(): void
    {
        $customerGroup = CustomerGroup::factory()->create();
        $code = DiscountCode::factory()->create([
            'customer_group_id' => $customerGroup->id,
        ]);

        $this->assertInstanceOf(\App\Models\CustomerGroup::class, $code->customerGroup);
        $this->assertEquals($customerGroup->id, $code->customerGroup->id);
    }

    public function test_discount_code_model_has_correct_attributes(): void
    {
        $code = DiscountCode::factory()->create([
            'code' => 'TEST123',
            'name' => 'Test Code',
            'type' => 'percentage',
            'value' => 15.5,
            'is_active' => true,
        ]);

        $this->assertEquals('TEST123', $code->code);
        $this->assertEquals('Test Code', $code->name);
        $this->assertEquals('percentage', $code->type);
        $this->assertEquals(15.5, $code->value);
        $this->assertTrue($code->is_active);
    }

    public function test_discount_code_can_be_duplicated(): void
    {
        $customerGroup = CustomerGroup::factory()->create();

        $originalCode = DiscountCode::factory()->create([
            'code' => 'ORIGINAL',
            'name' => 'Original Code',
            'customer_group_id' => $customerGroup->id,
        ]);

        $duplicatedCode = $originalCode->replicate();
        $duplicatedCode->code = $originalCode->code . '_copy_' . time();
        $duplicatedCode->name = $originalCode->name . ' (Copy)';
        $duplicatedCode->used_count = 0;
        $duplicatedCode->save();

        $this->assertDatabaseHas('discount_codes', [
            'code' => $duplicatedCode->code,
            'name' => 'Original Code (Copy)',
        ]);

        $this->assertNotEquals($originalCode->id, $duplicatedCode->id);
    }
}

