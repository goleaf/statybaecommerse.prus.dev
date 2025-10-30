<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\VariantAnalyticsResource\Pages\ListVariantAnalytics;
use App\Filament\Resources\VariantAttributeValueResource\Pages\ListVariantAttributeValues;
use App\Filament\Resources\VariantCombinationResource\Pages\ListVariantCombinations;
use App\Filament\Resources\VariantPriceHistoryResource\Pages\ListVariantPriceHistories;
use App\Filament\Resources\VariantPricingRuleResource\Pages\ListVariantPricingRules;
use App\Filament\Resources\VariantStockHistoryResource\Pages\ListVariantStockHistories;
use App\Models\User;
use App\Models\VariantAnalytics;
use App\Models\VariantAttributeValue;
use App\Models\VariantCombination;
use App\Models\VariantPriceHistory;
use App\Models\VariantPricingRule;
use App\Models\VariantStockHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Focused smoke coverage for the variant-centric Filament resources added in the v4 upgrade.
 */
final class VariantCatalogResourceCoverageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Filament runs inside the admin panel context before Livewire boots.
        $this->resolveAdminPanel();

        // Normalise localisation so seeded factories output deterministic English strings.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Authenticate as the canonical admin so resource policies grant access automatically.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    /**
     * @return array<string, array{class-string, string}>
     */
    public static function variantResourceProvider(): array
    {
        // Map each variant resource list page to the helper that prepares a visible record.
        return [
            'variant analytics'        => [ListVariantAnalytics::class, 'createVariantAnalyticsRecord'],
            'variant attribute values' => [ListVariantAttributeValues::class, 'createVariantAttributeValueRecord'],
            'variant combinations'     => [ListVariantCombinations::class, 'createVariantCombinationRecord'],
            'variant price histories'  => [ListVariantPriceHistories::class, 'createVariantPriceHistoryRecord'],
            'variant pricing rules'    => [ListVariantPricingRules::class, 'createVariantPricingRuleRecord'],
            'variant stock histories'  => [ListVariantStockHistories::class, 'createVariantStockHistoryRecord'],
        ];
    }

    /**
     * @dataProvider variantResourceProvider
     */
    public function test_variant_list_pages_render_seeded_records(string $pageClass, string $factoryMethod): void
    {
        // Seed a record via the dedicated helper so each table has content to assert against.
        $record = $this->{$factoryMethod}();

        // Hydrate the table explicitly to load deferred datasets before checking for the record.
        Livewire::test($pageClass)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$record]);
    }

    private function createVariantAnalyticsRecord(): VariantAnalytics
    {
        // Persist a deterministic analytics snapshot tied to a variant for the reporting grid.
        return VariantAnalytics::factory()->create([
            'date'        => now()->toDateString(),
            'date_bucket' => sprintf('%s:%s', VariantAnalytics::BUCKET_DAILY, now()->toDateString()),
            'views'       => 120,
            'clicks'      => 30,
        ]);
    }

    private function createVariantAttributeValueRecord(): VariantAttributeValue
    {
        // Create a localized attribute value so the attribute listing renders meaningful text columns.
        return VariantAttributeValue::factory()->create([
            'attribute_value'         => 'Coverage Attribute Value',
            'attribute_value_display' => 'Coverage Attribute Value Display',
            'attribute_value_slug'    => 'coverage-attribute-value',
            'is_filterable'           => true,
            'is_searchable'           => true,
        ]);
    }

    private function createVariantCombinationRecord(): VariantCombination
    {
        // Provision a combination with explicit attribute pairs so chip columns surface stable labels.
        return VariantCombination::factory()->create([
            'attribute_combinations' => [
                'color' => 'Coverage Blue',
                'size'  => 'Medium',
            ],
            'is_available' => true,
        ]);
    }

    private function createVariantPriceHistoryRecord(): VariantPriceHistory
    {
        // Generate a manual price change so historical pricing deltas appear in the index table.
        return VariantPriceHistory::factory()->manual()->create([
            'old_price' => 19.9900,
            'new_price' => 24.9900,
        ]);
    }

    private function createVariantPricingRuleRecord(): VariantPricingRule
    {
        // Persist a percentage-based pricing rule to keep discount columns populated during smoke checks.
        return VariantPricingRule::factory()->percentage()->create([
            'name'          => 'Coverage Pricing Rule',
            'value'         => 10.0,
            'is_active'     => true,
            'is_cumulative' => false,
        ]);
    }

    private function createVariantStockHistoryRecord(): VariantStockHistory
    {
        // Record an inventory increase so quantity deltas and reasons render with concrete numbers.
        return VariantStockHistory::factory()
            ->increase()
            ->withQuantities(5, 15)
            ->create([
                'change_reason'  => 'restock',
                'reference_type' => 'order',
                'reference_id'   => 42,
            ]);
    }
}
