<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Currency;
use App\Models\CustomerGroup;
use App\Models\Partner;
use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class PriceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_list_has_expected_fillable_and_casts(): void
    {
        // Instantiate the model instance so we can safely inspect its configuration arrays.
        $model = new PriceList;

        // Ensure the fillable definition protects against mass-assignment vulnerabilities.
        self::assertSame([
            'name',
            'code',
            'currency_id',
            'is_enabled',
            'priority',
            'starts_at',
            'ends_at',
            'description',
            'metadata',
            'is_default',
            'auto_apply',
            'min_order_amount',
            'max_order_amount',
        ], $model->getFillable());

        // Confirm the casts array automatically converts the key attributes into rich PHP types.
        $casts = $model->getCasts();

        foreach ([
            'is_enabled'       => 'boolean',
            'is_default'       => 'boolean',
            'auto_apply'       => 'boolean',
            'priority'         => 'integer',
            'starts_at'        => 'datetime',
            'ends_at'          => 'datetime',
            'min_order_amount' => 'decimal:2',
            'max_order_amount' => 'decimal:2',
            'metadata'         => 'array',
        ] as $attribute => $expectedCast) {
            self::assertArrayHasKey($attribute, $casts);
            self::assertSame($expectedCast, $casts[$attribute]);
        }
    }

    public function test_relationships_resolve_expected_models(): void
    {
        // Ensure the necessary tables and columns exist before exercising the relationships.
        $this->requireTableColumns([
            'price_lists'        => ['name', 'currency_id'],
            'price_list_items'   => ['price_list_id', 'net_amount'],
            'customer_groups'    => ['name'],
            'partners'           => ['name'],
            'group_price_list'   => ['group_id', 'price_list_id'],
            'partner_price_list' => ['partner_id', 'price_list_id'],
        ]);

        // Create the primary price list with a related currency and a couple of price list items.
        $priceList = $this->createPriceListRecord([
            'name'        => 'Relationship Matrix',
            'currency_id' => Currency::factory()->eur()->create(),
        ]);

        $this->createPriceListItemRecord($priceList);
        $this->createPriceListItemRecord($priceList);

        // Prepare customer groups and partners to attach through the pivot tables.
        $customerGroups = CustomerGroup::factory()->count(2)->create();
        $partners = Partner::factory()->count(2)->create();

        // Insert pivot records manually to accommodate legacy schemas that may not support attach().
        foreach ($customerGroups as $group) {
            DB::table('group_price_list')->insert([
                'group_id'      => $group->getKey(),
                'price_list_id' => $priceList->getKey(),
            ]);
        }

        foreach ($partners as $partner) {
            DB::table('partner_price_list')->insert([
                'partner_id'    => $partner->getKey(),
                'price_list_id' => $priceList->getKey(),
            ]);
        }

        $priceList->refresh();

        // Verify the pivot tables contain the expected rows before asserting the relationships.
        $groupPivotIds = DB::table('group_price_list')
            ->where('price_list_id', $priceList->getKey())
            ->pluck('group_id')
            ->all();
        $partnerPivotIds = DB::table('partner_price_list')
            ->where('price_list_id', $priceList->getKey())
            ->pluck('partner_id')
            ->all();

        // Validate the currency relationship returns the expected related model instance.
        self::assertInstanceOf(Currency::class, $priceList->currency);
        self::assertTrue($priceList->currency->is($priceList->currency()->first()));

        // Ensure the has-many relationship returns the created items collection.
        self::assertCount(2, $priceList->items);
        self::assertContainsOnlyInstancesOf(PriceListItem::class, $priceList->items);

        // The customer groups many-to-many relationship should return the attached models.
        self::assertEqualsCanonicalizing($customerGroups->modelKeys(), $groupPivotIds);
        self::assertEqualsCanonicalizing($groupPivotIds, $priceList->customerGroups()->pluck('id')->all());
        self::assertContainsOnlyInstancesOf(CustomerGroup::class, $priceList->customerGroups);

        // The partners many-to-many relationship should also return hydrated partner models.
        self::assertEqualsCanonicalizing($partners->modelKeys(), $partnerPivotIds);
        self::assertEqualsCanonicalizing($partnerPivotIds, $priceList->partners()->pluck('id')->all());
        self::assertContainsOnlyInstancesOf(Partner::class, $priceList->partners);
    }

    public function test_scope_helpers_filter_and_order_records(): void
    {
        // Skip the scope verification if the minimal schema support is unavailable.
        $this->requireTableColumns([
            'price_lists' => ['name', 'is_enabled', 'priority'],
        ]);

        // Freeze time to ensure deterministic assertions for scopes that depend on now().
        Carbon::setTestNow('2024-01-15 12:00:00');

        // Seed price lists covering the different states each scope should address.
        $records = collect();

        $enabled = $this->createPriceListRecord([
            'name'       => 'Alpha',
            'is_enabled' => true,
            'priority'   => 10,
        ]);
        $records->push($enabled);

        $disabled = $this->createPriceListRecord([
            'name'       => 'Bravo',
            'is_enabled' => false,
            'priority'   => 50,
        ]);
        $records->push($disabled);

        $default = null;
        if ($this->tableSupportsColumns('price_lists', ['is_default', 'auto_apply'])) {
            $default = $this->createPriceListRecord([
                'name'       => 'Charlie',
                'is_default' => true,
                'auto_apply' => true,
                'is_enabled' => true,
                'priority'   => 5,
            ]);
            $records->push($default);
        }

        $byCurrency = null;
        if ($this->tableSupportsColumns('price_lists', ['currency_id'])) {
            $byCurrency = $this->createPriceListRecord([
                'name'        => 'Delta',
                'currency_id' => Currency::factory()->create(),
                'is_enabled'  => true,
                'priority'    => 30,
            ]);
            $records->push($byCurrency);
        }

        $active = $future = $expired = null;
        if ($this->tableSupportsColumns('price_lists', ['starts_at', 'ends_at'])) {
            $active = $this->createPriceListRecord([
                'name'       => 'Echo',
                'is_enabled' => true,
                'starts_at'  => Carbon::now()->subDay(),
                'ends_at'    => Carbon::now()->addDay(),
                'priority'   => 20,
            ]);
            $records->push($active);

            $future = $this->createPriceListRecord([
                'name'       => 'Foxtrot',
                'is_enabled' => true,
                'starts_at'  => Carbon::now()->addDay(),
                'priority'   => 60,
            ]);
            $records->push($future);

            $expired = $this->createPriceListRecord([
                'name'       => 'Golf',
                'is_enabled' => true,
                'starts_at'  => Carbon::now()->subDays(10),
                'ends_at'    => Carbon::now()->subDay(),
                'priority'   => 40,
            ]);
            $records->push($expired);
        }

        $amountLimited = null;
        if ($this->tableSupportsColumns('price_lists', ['min_order_amount', 'max_order_amount'])) {
            $amountLimited = $this->createPriceListRecord([
                'name'             => 'Hotel',
                'is_enabled'       => true,
                'min_order_amount' => 50.00,
                'max_order_amount' => 150.00,
                'priority'         => 70,
            ]);
            $records->push($amountLimited);
        }

        // Collect helper for repeated assertions without duplicating array unpacking logic.
        $pluckIds = static fn (Collection $collection): array => $collection->pluck('id')->all();

        // Enabled scope should exclude disabled records.
        $enabledFromScope = PriceList::query()->enabled()->orderedByName()->get();
        self::assertTrue($enabledFromScope->every(static fn (PriceList $priceList) => (bool) $priceList->is_enabled));
        $enabledNames = $enabledFromScope->pluck('name')->values()->all();
        $sortedEnabledNames = $enabledNames;
        sort($sortedEnabledNames);
        self::assertSame($sortedEnabledNames, $enabledNames);

        // Active scope should only return the currently active record when the scheduling columns are present.
        if ($active !== null) {
            self::assertSame([$active->getKey()], $pluckIds(PriceList::query()->active()->get()));
        }

        // Default scope should surface price lists marked as default when supported by the schema.
        if ($default !== null) {
            self::assertSame([$default->getKey()], $pluckIds(PriceList::query()->default()->get()));
            self::assertSame([$default->getKey()], $pluckIds(PriceList::query()->autoApply()->get()));
        }

        // Currency scope should filter by the provided currency identifier when available.
        if ($byCurrency !== null && Schema::hasColumn('price_lists', 'currency_id')) {
            self::assertSame([$byCurrency->getKey()], $pluckIds(PriceList::query()->byCurrency($byCurrency->currency_id)->get()));
        }

        // By priority scope should follow the requested ordering direction using all created records.
        $prioritiesFromScope = PriceList::query()->byPriority('desc')->pluck('priority')->values()->all();
        $sortedPriorities = $prioritiesFromScope;
        rsort($sortedPriorities);
        self::assertSame($sortedPriorities, $prioritiesFromScope);

        // Ordered by name scope should respect ascending and descending orders for all records.
        $nameAscFromScope = PriceList::query()->orderedByName()->pluck('name')->values()->all();
        $sortedAscNames = $nameAscFromScope;
        sort($sortedAscNames);
        self::assertSame($sortedAscNames, $nameAscFromScope);

        $nameDescFromScope = PriceList::query()->orderedByName('desc')->pluck('name')->values()->all();
        $sortedDescNames = $nameDescFromScope;
        rsort($sortedDescNames);
        self::assertSame($sortedDescNames, $nameDescFromScope);

        // For order amount scope should filter down to the price list supporting the value when columns are present.
        if ($amountLimited !== null) {
            $orderAmountScope = PriceList::query()->forOrderAmount(75.00)->get();
            self::assertTrue($orderAmountScope->every(static function (PriceList $priceList): bool {
                $minValid = $priceList->min_order_amount === null || $priceList->min_order_amount <= 75.00;
                $maxValid = $priceList->max_order_amount === null || $priceList->max_order_amount >= 75.00;

                return $minValid && $maxValid;
            }));
            self::assertContains($amountLimited->getKey(), $orderAmountScope->pluck('id')->all());
        }

        // Reset the test clock to avoid polluting other tests.
        Carbon::setTestNow();
    }

    public function test_business_helpers_calculate_expected_flags_and_prices(): void
    {
        // Ensure the necessary schema support exists for the helper method validation.
        $this->requireTableColumns([
            'price_lists'      => ['is_enabled', 'starts_at', 'ends_at', 'is_default', 'auto_apply'],
            'price_list_items' => ['price_list_id', 'product_id', 'variant_id', 'net_amount'],
            'products'         => ['name'],
            'product_variants' => ['product_id'],
        ]);

        // Freeze now so the isActive helper behaves deterministically across assertions.
        Carbon::setTestNow('2024-02-01 09:00:00');

        // Create the base price list that should evaluate as active.
        $priceList = $this->createPriceListRecord([
            'is_enabled' => true,
            'starts_at'  => Carbon::now()->subDay(),
            'ends_at'    => Carbon::now()->addDay(),
            'is_default' => true,
            'auto_apply' => true,
        ]);

        // Create fixtures for the product and variant price checks.
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create();

        // Attach a price list item that references both the product and variant to verify lookups.
        $item = $this->createPriceListItemRecord($priceList, [
            'product_id' => $product,
            'variant_id' => $variant,
            'net_amount' => 123.45,
        ]);
        $priceList->refresh();

        // Verify the helper flags and computed accessors for the populated price list.
        self::assertTrue($priceList->isActive());
        self::assertTrue($priceList->isDefault());
        self::assertTrue($priceList->canAutoApply());
        self::assertSame(123.45, $priceList->getEffectivePriceForProduct($product));
        self::assertSame(123.45, $priceList->getEffectivePriceForVariant($variant));
        self::assertSame(1, $priceList->items_count);
        self::assertSame(0, $priceList->customer_groups_count);
        self::assertSame(0, $priceList->partners_count);

        // Create additional price lists that should fail the active check for various reasons.
        $disabled = $this->createPriceListRecord(['is_enabled' => false]);
        $future = $this->createPriceListRecord(['is_enabled' => true, 'starts_at' => Carbon::now()->addDay()]);
        $expired = $this->createPriceListRecord(['is_enabled' => true, 'ends_at' => Carbon::now()->subDay()]);

        // Confirm the helper methods correctly identify the inactive states and missing price items.
        self::assertFalse($disabled->isActive());
        self::assertFalse($future->isActive());
        self::assertFalse($expired->isActive());
        self::assertNull($priceList->getEffectivePriceForProduct(Product::factory()->create()));
        self::assertNull($priceList->getEffectivePriceForVariant(ProductVariant::factory()->create()));

        // Detach the test now timestamp to avoid affecting other tests.
        Carbon::setTestNow();
    }

    /**
     * Ensure the schema contains the required tables and columns or skip the test early.
     *
     * @param array<string, array<int, string>> $requirements
     */
    private function requireTableColumns(array $requirements): void
    {
        foreach ($requirements as $table => $columns) {
            if (! Schema::hasTable($table)) {
                $this->markTestSkipped("Required table [{$table}] is missing for this test.");
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    $this->markTestSkipped("Required column [{$table}.{$column}] is missing for this test.");
                }
            }
        }
    }

    /**
     * Check whether the provided table exposes every required column.
     *
     * @param array<int, string> $columns
     */
    private function tableSupportsColumns(string $table, array $columns): bool
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create a price list record with attributes filtered to match the current schema.
     *
     * @param array<string, mixed> $overrides
     */
    private function createPriceListRecord(array $overrides = []): PriceList
    {
        $attributes = array_merge([
            'name'             => 'Test Price List',
            'is_enabled'       => true,
            'priority'         => 10,
            'starts_at'        => null,
            'ends_at'          => null,
            'description'      => 'Test price list description',
            'metadata'         => ['source' => 'test', 'segment' => 'unit'],
            'is_default'       => false,
            'auto_apply'       => false,
            'min_order_amount' => null,
            'max_order_amount' => null,
        ], $overrides);

        if (Schema::hasColumn('price_lists', 'code')) {
            $attributes['code'] = $attributes['code'] ?? 'PL-' . random_int(1000, 9999);
        } else {
            unset($attributes['code']);
        }

        if (Schema::hasColumn('price_lists', 'currency_id')) {
            $currency = $attributes['currency_id'] ?? Currency::factory()->create();
            if ($currency instanceof Currency) {
                $attributes['currency_id'] = $currency->getKey();
            }
        } else {
            unset($attributes['currency_id']);
        }

        foreach ([
            'priority',
            'starts_at',
            'ends_at',
            'description',
            'metadata',
            'is_default',
            'auto_apply',
            'min_order_amount',
            'max_order_amount',
        ] as $column) {
            if (! Schema::hasColumn('price_lists', $column)) {
                unset($attributes[$column]);
            }
        }

        return PriceList::query()->create($attributes);
    }

    /**
     * Persist a price list item tailored to the current schema support.
     *
     * @param array<string, mixed> $overrides
     */
    private function createPriceListItemRecord(PriceList $priceList, array $overrides = []): PriceListItem
    {
        $attributes = array_merge([
            'price_list_id' => $priceList->getKey(),
            'net_amount'    => 123.45,
            'is_active'     => true,
            'product_id'    => null,
            'variant_id'    => null,
        ], $overrides);

        if (Schema::hasColumn('price_list_items', 'product_id')) {
            if ($attributes['product_id'] instanceof Product) {
                $attributes['product_id'] = $attributes['product_id']->getKey();
            }
        } else {
            unset($attributes['product_id']);
        }

        if (Schema::hasColumn('price_list_items', 'variant_id')) {
            if ($attributes['variant_id'] instanceof ProductVariant) {
                $attributes['variant_id'] = $attributes['variant_id']->getKey();
            }
        } else {
            unset($attributes['variant_id']);
        }

        foreach ([
            'compare_amount',
            'name',
            'description',
            'notes',
            'valid_from',
            'valid_until',
            'min_quantity',
            'max_quantity',
            'priority',
        ] as $column) {
            if (! Schema::hasColumn('price_list_items', $column)) {
                unset($attributes[$column]);
            }
        }

        return PriceListItem::query()->create($attributes);
    }
}
