<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Discounts\DiscountPresetRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Tests\TestCase;

/**
 * @covers \App\Support\Discounts\DiscountPresetRepository
 */
final class DiscountPresetRepositoryTest extends TestCase
{
    public function test_it_seeds_defaults_when_storage_missing(): void
    {
        // Freeze time to keep created_at assertions deterministic.
        Carbon::setTestNow('2024-01-01 12:00:00');

        Storage::fake('local');

        config()->set('discount_presets.defaults', [
            [
                'name'        => 'Test Default',
                'description' => 'Default preset for testing.',
                'type'        => 'percentage',
                'value'       => 12.5,
                'conditions'  => ['customer_group:testers'],
            ],
        ]);

        $repository = app(DiscountPresetRepository::class);

        $presets = $repository->all();

        $this->assertCount(1, $presets);
        $this->assertSame('Test Default', $presets[0]['name']);
        $this->assertSame('percentage', $presets[0]['type']);
        $this->assertSame(12.5, $presets[0]['value']);
        $this->assertSame(['customer_group:testers'], $presets[0]['conditions']);

        // Ensure the defaults are persisted so future reads use the stored file.
        Storage::disk('local')->assertExists('discount-presets.json');

        Carbon::setTestNow();
    }

    public function test_it_persists_created_presets(): void
    {
        Carbon::setTestNow('2024-01-01 12:00:00');

        Storage::fake('local');

        config()->set('discount_presets.defaults', []);

        $repository = app(DiscountPresetRepository::class);

        $created = $repository->create([
            'name'        => 'Launch Promo',
            'description' => 'Introductory promotion.',
            'type'        => 'fixed',
            'value'       => 20,
            'conditions'  => ['applies_to:launch'],
        ]);

        $this->assertSame('Launch Promo', $created['name']);
        $this->assertSame('fixed', $created['type']);
        $this->assertSame(20.0, $created['value']);
        $this->assertNotEmpty($created['id']);

        try {
            $stored = json_decode(
                Storage::disk('local')->get('discount-presets.json'),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            $this->fail('Failed to decode stored presets: ' . $exception->getMessage());
        }

        $this->assertIsArray($stored);
        $this->assertSame($created['id'], $stored[0]['id']);
        $this->assertSame(['applies_to:launch'], $stored[0]['conditions']);

        Carbon::setTestNow();
    }
}
