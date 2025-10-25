<?php declare(strict_types=1);

namespace Tests\Models;

use App\Models\Currency;
use App\Models\Price;
use App\Models\Product;
use App\Models\Translations\PriceTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class PriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_has_expected_configuration(): void
    {
        // Instantiate the model without persisting to inspect its guarded configuration.
        $model = new Price();

        // Validate the mass-assignable attributes to guard against accidental over-posting.
        self::assertSame([
            'priceable_id',
            'priceable_type',
            'currency_id',
            'amount',
            'compare_amount',
            'cost_amount',
            'type',
            'starts_at',
            'ends_at',
            'is_enabled',
            'metadata',
        ], $model->getFillable());

        // Confirm all critical casts are defined for monetary precision and boolean/date handling.
        $casts = $model->getCasts();
        self::assertSame('decimal:4', $casts['amount']);
        self::assertSame('decimal:4', $casts['compare_amount']);
        self::assertSame('decimal:4', $casts['cost_amount']);
        self::assertSame('datetime', $casts['starts_at']);
        self::assertSame('datetime', $casts['ends_at']);
        self::assertSame('boolean', $casts['is_enabled']);
        self::assertSame('array', $casts['metadata']);
    }

    public function test_relationships_return_expected_models(): void
    {
        // Freeze time to keep timestamps deterministic for the created records.
        Carbon::setTestNow('2025-01-01 10:00:00');

        // Create the supporting product and currency that the price will reference.
        $product = Product::factory()->create();
        $currency = Currency::factory()->create(['code' => 'EUR']);

        // Persist the price record and attach a translated label to exercise all relations.
        $price = Price::factory()->create([
            'priceable_type' => $product->getMorphClass(),
            'priceable_id' => $product->getKey(),
            'currency_id' => $currency->getKey(),
        ]);

        $translation = $price->translations()->create([
            'locale' => 'en',
            'name' => 'Retail price',
            'description' => 'Retail price description',
        ]);

        // Reload the relations to ensure we are asserting against hydrated models.
        $price = $price->fresh(['priceable', 'currency', 'translations']);

        // Confirm the polymorphic relation hydrates the originating product model.
        self::assertInstanceOf(Product::class, $price->priceable);
        self::assertTrue($product->is($price->priceable));

        // Validate the currency relation matches the created record.
        self::assertInstanceOf(Currency::class, $price->currency);
        self::assertTrue($currency->is($price->currency));

        // Ensure the translation relation returns the stored localized entry.
        self::assertInstanceOf(PriceTranslation::class, $translation);
        self::assertTrue(
            $price->translations->contains(static fn (PriceTranslation $related): bool => $related->is($translation))
        );

        Carbon::setTestNow();
    }

    public function test_scopes_filter_price_records_correctly(): void
    {
        // Freeze time so that active window calculations share the same reference point.
        Carbon::setTestNow('2025-01-02 12:00:00');

        // Prepare shared references for the created price records.
        $product = Product::factory()->create();
        $eur = Currency::factory()->create(['code' => 'EUR']);
        $usd = Currency::factory()->create(['code' => 'USD']);

        // Build a variety of prices that cover enabled, disabled, active, and expired scenarios.
        $active = Price::factory()->create([
            'priceable_type' => $product->getMorphClass(),
            'priceable_id' => $product->getKey(),
            'currency_id' => $eur->getKey(),
            'is_enabled' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $disabled = Price::factory()->create([
            'priceable_type' => $product->getMorphClass(),
            'priceable_id' => $product->getKey(),
            'currency_id' => $eur->getKey(),
            'is_enabled' => false,
        ]);

        $expired = Price::factory()->create([
            'priceable_type' => $product->getMorphClass(),
            'priceable_id' => $product->getKey(),
            'currency_id' => $eur->getKey(),
            'is_enabled' => true,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->subDay(),
        ]);

        $usdActive = Price::factory()->create([
            'priceable_type' => $product->getMorphClass(),
            'priceable_id' => $product->getKey(),
            'currency_id' => $usd->getKey(),
            'is_enabled' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(2),
        ]);

        // The enabled scope should include all records with the boolean flag set.
        $enabledIds = Price::query()->enabled()->pluck('id');
        self::assertTrue($enabledIds->contains($active->getKey()));
        self::assertTrue($enabledIds->contains($expired->getKey()));
        self::assertTrue($enabledIds->contains($usdActive->getKey()));
        self::assertFalse($enabledIds->contains($disabled->getKey()));

        // The active scope should only include enabled prices that fall within the active window.
        $activeIds = Price::query()->active()->pluck('id');
        self::assertTrue($activeIds->contains($active->getKey()));
        self::assertTrue($activeIds->contains($usdActive->getKey()));
        self::assertFalse($activeIds->contains($expired->getKey()));
        self::assertFalse($activeIds->contains($disabled->getKey()));

        // Filtering by currency should restrict the query to the requested ISO code.
        $eurIds = Price::query()->forCurrency('EUR')->pluck('id');
        self::assertTrue($eurIds->contains($active->getKey()));
        self::assertTrue($eurIds->contains($disabled->getKey()));
        self::assertTrue($eurIds->contains($expired->getKey()));
        self::assertFalse($eurIds->contains($usdActive->getKey()));

        // Combining scopes should allow us to pinpoint the single active EUR price record.
        $activeEurIds = Price::query()->forCurrency('EUR')->active()->pluck('id')->all();
        self::assertEqualsCanonicalizing([$active->getKey()], $activeEurIds);

        Carbon::setTestNow();
    }

    public function test_is_active_and_discount_percentage_behaviour(): void
    {
        // Keep temporal comparisons deterministic for the accessor checks.
        Carbon::setTestNow('2025-01-03 08:30:00');

        // Provision the required related records.
        $product = Product::factory()->create();
        $currency = Currency::factory()->create(['code' => 'EUR']);

        // Create a baseline active price with a discount applied.
        $price = Price::factory()->create([
            'priceable_type' => $product->getMorphClass(),
            'priceable_id' => $product->getKey(),
            'currency_id' => $currency->getKey(),
            'amount' => 100,
            'compare_amount' => 150,
            'is_enabled' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        // The accessors should report an active price and the rounded discount percentage.
        self::assertTrue($price->isActive());
        self::assertSame(33, $price->discount_percentage);

        // When no discount is configured the accessor should return null.
        $price->forceFill(['compare_amount' => 100]);
        self::assertNull($price->discount_percentage);

        // Changing the monetary values should recompute the percentage dynamically.
        $price->forceFill(['amount' => 150, 'compare_amount' => 200]);
        self::assertSame(25, $price->discount_percentage);

        // Toggle various state flags to ensure the active check behaves as expected.
        $price->forceFill(['is_enabled' => false]);
        self::assertFalse($price->isActive());

        $price->forceFill(['is_enabled' => true, 'starts_at' => now()->addHour()]);
        self::assertFalse($price->isActive());

        $price->forceFill(['starts_at' => now()->subDays(2), 'ends_at' => now()->subHour()]);
        self::assertFalse($price->isActive());

        $price->forceFill(['ends_at' => now()->addDay()]);
        self::assertTrue($price->isActive());

        Carbon::setTestNow();
    }

    public function test_translation_helpers_respect_locale_and_loaded_relations(): void
    {
        // Fix the clock so translation timestamps remain predictable.
        Carbon::setTestNow('2025-01-04 09:45:00');

        // Set up the underlying price record and attach multiple translations.
        $product = Product::factory()->create();
        $currency = Currency::factory()->create(['code' => 'EUR']);
        $price = Price::factory()->create([
            'priceable_type' => $product->getMorphClass(),
            'priceable_id' => $product->getKey(),
            'currency_id' => $currency->getKey(),
        ]);

        $price->translations()->create([
            'locale' => 'en',
            'name' => 'English Retail',
            'description' => 'English retail description',
        ]);

        $price->translations()->create([
            'locale' => 'lt',
            'name' => 'Lietuviškas mažmeninis',
            'description' => 'Lietuviškas mažmeninės kainos aprašymas',
        ]);

        // Load translations once to ensure the helper reuses the cached relation.
        $price->load('translations');

        // Default locale should follow the application setting.
        app()->setLocale('en');
        self::assertSame('English Retail', $price->getTranslatedName());
        self::assertSame('English retail description', $price->getTranslatedDescription());

        // Explicit locale arguments should override the application default.
        self::assertSame('Lietuviškas mažmeninis', $price->getTranslatedName('lt'));
        self::assertSame('Lietuviškas mažmeninės kainos aprašymas', $price->getTranslatedDescription('lt'));

        // Requesting an unavailable locale should gracefully return null values.
        self::assertNull($price->getTranslatedName('lv'));
        self::assertNull($price->getTranslatedDescription('lv'));

        Carbon::setTestNow();
    }
}

