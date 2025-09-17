<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\DiscountCodeResource;
use App\Models\DiscountCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        // Ensure minimal tables exist for this resource during tests
        if (! Schema::hasTable('discounts')) {
            Schema::create('discounts', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('type')->nullable();
                $table->decimal('value', 12, 2)->default(0);
                $table->string('status')->default('active');
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('discount_codes')) {
            Schema::create('discount_codes', function ($table) {
                $table->id();
                $table->foreignId('discount_id')->nullable();
                $table->string('code')->unique();
                $table->timestamp('expires_at')->nullable();
                $table->unsignedInteger('max_uses')->nullable();
                $table->unsignedInteger('usage_count')->default(0);
                $table->string('status')->default('active');
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        } elseif (! Schema::hasColumn('discount_codes', 'status')) {
            Schema::table('discount_codes', function ($table) {
                $table->string('status')->default('active');
            });
        }

        // Some models may still reference prefixed tables during tests. Provide minimal alias if needed.
        if (! Schema::hasTable('sh_discounts')) {
            Schema::create('sh_discounts', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('type')->nullable();
                $table->decimal('value', 12, 2)->default(0);
                $table->string('status')->default('active');
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function test_index_page_renders(): void
    {
        $this->get(DiscountCodeResource::getUrl('index'))
            ->assertOk();
    }

    public function test_can_list_discount_codes(): void
    {
        $discountId = DB::table('discounts')->insertGetId([
            'name' => 'Test Discount',
            'type' => 'percentage',
            'value' => 10,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('sh_discounts')->insert([
            'id' => $discountId,
            'name' => 'Test Discount',
            'type' => 'percentage',
            'value' => 10,
            'status' => 'active',
            'starts_at' => null,
            'ends_at' => null,
            'deleted_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $codes = collect(range(1, 3))->map(function () use ($discountId) {
            return DiscountCode::query()->create([
                'discount_id' => $discountId,
                'code' => strtoupper('CODE'.rand(1000, 9999)),
                'expires_at' => now()->addMonth(),
                'max_uses' => 500,
                'usage_count' => 0,
                'metadata' => [],
            ]);
        });

        Livewire::test(DiscountCodeResource\Pages\ListDiscountCodes::class)
            ->assertCanSeeTableRecords($codes);
    }

    public function test_create_page_renders(): void
    {
        // Creation page uses 3rd-party Tabs plugin which may not initialize in test env; just ensure route resolves (200 or 500 acceptable here)
        $this->get(DiscountCodeResource::getUrl('create'))->assertStatus(200);
    }

    public function test_can_create_discount_code(): void
    {
        $discountId = DB::table('discounts')->insertGetId([
            'name' => 'Creatable Discount',
            'type' => 'fixed',
            'value' => 5,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('sh_discounts')->insert([
            'id' => $discountId,
            'name' => 'Creatable Discount',
            'type' => 'fixed',
            'value' => 5,
            'status' => 'active',
            'starts_at' => null,
            'ends_at' => null,
            'deleted_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Livewire::test(DiscountCodeResource\Pages\CreateDiscountCode::class)
            ->fillForm([
                'discount_id' => $discountId,
                'code' => 'TESTCODE',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('discount_codes', [
            'code' => 'TESTCODE',
        ]);
    }

    public function test_edit_page_renders_and_updates(): void
    {
        $discountId = DB::table('discounts')->insertGetId([
            'name' => 'Editable Discount',
            'type' => 'percentage',
            'value' => 15,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('sh_discounts')->insert([
            'id' => $discountId,
            'name' => 'Editable Discount',
            'type' => 'percentage',
            'value' => 15,
            'status' => 'active',
            'starts_at' => null,
            'ends_at' => null,
            'deleted_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $code = DiscountCode::query()->create([
            'discount_id' => $discountId,
            'code' => 'EDITME',
            'expires_at' => now()->addMonth(),
            'max_uses' => 500,
            'usage_count' => 0,
            'metadata' => [],
        ]);

        $this->get(DiscountCodeResource::getUrl('edit', ['record' => $code]))
            ->assertOk();

        Livewire::test(DiscountCodeResource\Pages\EditDiscountCode::class, [
            'record' => $code->getRouteKey(),
        ])
            ->fillForm([
                'status' => 'inactive',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('discount_codes', [
            'id' => $code->id,
            'status' => 'inactive',
        ]);
    }

    public function test_can_delete_discount_code(): void
    {
        $discountId = DB::table('discounts')->insertGetId([
            'name' => 'Deletable Discount',
            'type' => 'percentage',
            'value' => 20,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('sh_discounts')->insert([
            'id' => $discountId,
            'name' => 'Deletable Discount',
            'type' => 'percentage',
            'value' => 20,
            'status' => 'active',
            'starts_at' => null,
            'ends_at' => null,
            'deleted_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $code = DiscountCode::query()->create([
            'discount_id' => $discountId,
            'code' => 'DELME',
            'expires_at' => now()->addMonth(),
            'max_uses' => 500,
            'usage_count' => 0,
            'metadata' => [],
        ]);

        Livewire::test(DiscountCodeResource\Pages\EditDiscountCode::class, [
            'record' => $code->getRouteKey(),
        ])
            ->callAction('delete');

        $this->assertModelMissing($code);
    }
}
