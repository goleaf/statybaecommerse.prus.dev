<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Discount;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_discount_can_be_created(): void
    {
        $discount = Discount::factory()->create([
            'name' => 'Test Discount',
            'type' => 'percentage',
            'value' => 10.00,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('discounts', [
            'name' => 'Test Discount',
            'type' => 'percentage',
            'value' => 10.00,
            'is_active' => true,
        ]);
    }

    public function test_discount_casts_work_correctly(): void
    {
        $discount = Discount::factory()->create([
            'value' => 15.50,
            'is_active' => true,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);

        $this->assertIsNumeric($discount->value);
        $this->assertIsBool($discount->is_active);
        $this->assertInstanceOf(\Carbon\Carbon::class, $discount->starts_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $discount->ends_at);
    }

    public function test_discount_fillable_attributes(): void
    {
        $discount = new Discount();
        $fillable = $discount->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('value', $fillable);
        $this->assertContains('is_active', $fillable);
    }

    public function test_discount_type_enum(): void
    {
        $percentageDiscount = Discount::factory()->create(['type' => 'percentage']);
        $fixedDiscount = Discount::factory()->create(['type' => 'fixed']);

        $this->assertEquals('percentage', $percentageDiscount->type);
        $this->assertEquals('fixed', $fixedDiscount->type);
    }

    public function test_discount_is_active_scope(): void
    {
        $activeDiscount = Discount::factory()->create(['is_active' => true]);
        $inactiveDiscount = Discount::factory()->create(['is_active' => false]);

        $activeDiscounts = Discount::active()->get();

        $this->assertTrue($activeDiscounts->contains($activeDiscount));
        $this->assertFalse($activeDiscounts->contains($inactiveDiscount));
    }

    public function test_discount_is_valid_scope(): void
    {
        $validDiscount = Discount::factory()->create([
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $expiredDiscount = Discount::factory()->create([
            'is_active' => true,
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDay(),
        ]);

        $futureDiscount = Discount::factory()->create([
            'is_active' => true,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);

        $validDiscounts = Discount::valid()->get();

        $this->assertTrue($validDiscounts->contains($validDiscount));
        $this->assertFalse($validDiscounts->contains($expiredDiscount));
        $this->assertFalse($validDiscounts->contains($futureDiscount));
    }

    public function test_discount_can_have_products(): void
    {
        $discount = Discount::factory()->create();
        $products = Product::factory()->count(3)->create();

        $discount->products()->attach($products->pluck('id'));

        $this->assertCount(3, $discount->products);
        $this->assertInstanceOf(Product::class, $discount->products->first());
    }

    public function test_discount_can_have_users(): void
    {
        $discount = Discount::factory()->create();
        $users = User::factory()->count(2)->create();

        $discount->users()->attach($users->pluck('id'));

        $this->assertCount(2, $discount->users);
        $this->assertInstanceOf(User::class, $discount->users->first());
    }

    public function test_discount_calculate_amount(): void
    {
        $percentageDiscount = Discount::factory()->create([
            'type' => 'percentage',
            'value' => 20.00,
        ]);

        $fixedDiscount = Discount::factory()->create([
            'type' => 'fixed',
            'value' => 15.00,
        ]);

        $this->assertEquals(20.00, $percentageDiscount->calculateAmount(100.00));
        $this->assertEquals(15.00, $fixedDiscount->calculateAmount(100.00));
    }

    public function test_discount_has_usage_limit(): void
    {
        $discount = Discount::factory()->create([
            'usage_limit' => 100,
            'usage_count' => 50,
        ]);

        $this->assertEquals(100, $discount->usage_limit);
        $this->assertEquals(50, $discount->usage_count);
        $this->assertTrue($discount->hasUsageLimit());
        $this->assertTrue($discount->canBeUsed());
    }

    public function test_discount_is_expired(): void
    {
        $expiredDiscount = Discount::factory()->create([
            'ends_at' => now()->subDay(),
        ]);

        $activeDiscount = Discount::factory()->create([
            'ends_at' => now()->addDay(),
        ]);

        $this->assertTrue($expiredDiscount->isExpired());
        $this->assertFalse($activeDiscount->isExpired());
    }

    public function test_discount_is_not_started(): void
    {
        $futureDiscount = Discount::factory()->create([
            'starts_at' => now()->addDay(),
        ]);

        $activeDiscount = Discount::factory()->create([
            'starts_at' => now()->subDay(),
        ]);

        $this->assertTrue($futureDiscount->isNotStarted());
        $this->assertFalse($activeDiscount->isNotStarted());
    }
}