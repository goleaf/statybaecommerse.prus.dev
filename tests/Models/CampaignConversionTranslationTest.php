<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\CampaignConversion;
use App\Models\CampaignConversionTranslation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CampaignConversionTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes_are_well_defined(): void
    {
        // Having explicit expectations keeps factories aligned with the model's contract.
        $model = new CampaignConversionTranslation;

        self::assertSame([
            'campaign_conversion_id',
            'locale',
            'notes',
            'custom_attributes',
        ], $model->getFillable());
    }

    public function test_custom_attributes_are_cast_to_array(): void
    {
        // Persist a record with complex custom attributes and confirm the cast happens.
        $translation = CampaignConversionTranslation::factory()->create([
            'custom_attributes' => ['foo' => 'bar'],
        ]);

        self::assertIsArray($translation->custom_attributes);
        self::assertSame('array', $translation->getCasts()['custom_attributes']);
    }

    public function test_it_belongs_to_a_campaign_conversion(): void
    {
        // Creating a translation should hydrate the belongsTo relation.
        $translation = CampaignConversionTranslation::factory()->create();

        self::assertInstanceOf(BelongsTo::class, $translation->campaignConversion());
        self::assertInstanceOf(CampaignConversion::class, $translation->campaignConversion);
    }

    public function test_scope_for_locale_filters_translations(): void
    {
        // Seed translations for two locales to verify the scope behaviour.
        $english = CampaignConversionTranslation::factory()->create(['locale' => 'en']);
        $lithuanian = CampaignConversionTranslation::factory()->create(['locale' => 'lt']);

        $scoped = CampaignConversionTranslation::query()->forLocale('en')->get();

        self::assertTrue($scoped->contains($english));
        self::assertFalse($scoped->contains($lithuanian));
    }
}
