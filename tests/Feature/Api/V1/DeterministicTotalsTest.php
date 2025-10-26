<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\City;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class DeterministicTotalsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_quote_returns_structured_breakdown(): void
    {
        [$country, $regionCode, $postalCode] = $this->prepareDestination('LT-VL', '01100', 21.0);

        $payload = [
            'destination' => [
                'country' => $country->cca2,
                'region' => $regionCode,
                'postal_code' => $postalCode,
            ],
            'service' => 'standard',
            'items' => [
                ['quantity' => 2, 'unit_price' => 19.99],
                ['quantity' => 1, 'unit_price' => 5.00],
            ],
        ];

        $response = $this->postJson(route('api.v1.totals.deterministic'), $payload);

        $response->assertOk()
            ->assertJsonPath('contract', 'deterministic-totals')
            ->assertJsonPath('version', 'v1')
            ->assertJsonStructure([
                'data' => [
                    'totals' => ['subtotal', 'discount', 'taxable_amount', 'tax', 'shipping', 'total', 'currency', 'vat_rate'],
                    'components' => ['tax' => ['basis', 'rate', 'amount', 'origin', 'source'], 'shipping' => ['service', 'label', 'amount'], 'discount' => ['amount']],
                    'rounding' => ['precision', 'mode'],
                ],
                'meta' => ['generated_at'],
            ]);

        // Assert the totals reflect the deterministic price calculator output and rounding rules.
        $data = $response->json('data');
        self::assertSame(44.98, $data['totals']['subtotal']);
        self::assertSame(0.0, $data['totals']['discount']);
        self::assertSame(44.98, $data['totals']['taxable_amount']);
        self::assertSame(9.45, $data['totals']['tax']);
        self::assertSame(6.95, $data['totals']['shipping']);
        self::assertSame(61.38, $data['totals']['total']);
        self::assertSame('EUR', $data['totals']['currency']);
        self::assertSame(0.21, $data['totals']['vat_rate']);
        self::assertSame('standard', $data['components']['shipping']['service']);
        self::assertSame('region', $data['components']['tax']['origin']);
        self::assertSame(2, $data['rounding']['precision']);
        self::assertSame('half_up', $data['rounding']['mode']);
    }

    public function test_unknown_postcode_returns_422(): void
    {
        [$country, $regionCode] = $this->prepareDestination('LT-KA', '44100', 21.0);

        $payload = [
            'destination' => [
                'country' => $country->cca2,
                'region' => $regionCode,
                'postal_code' => '99999',
            ],
            'service' => 'express',
            'items' => [
                ['quantity' => 1, 'unit_price' => 10],
            ],
        ];

        $response = $this->postJson(route('api.v1.totals.deterministic'), $payload);

        // Unknown postal codes must fail validation so downstream systems cannot guess rates.
        $response->assertStatus(422);

        $violations = collect($response->json('error.context.violations'));
        self::assertNotNull($violations->firstWhere('field', 'destination.postal_code'));
    }

    public function test_rate_tampering_returns_409(): void
    {
        [$country, $regionCode, $postalCode] = $this->prepareDestination('LT-KL', '91200', 21.0);

        $payload = [
            'destination' => [
                'country' => $country->cca2,
                'region' => $regionCode,
                'postal_code' => $postalCode,
            ],
            'service' => 'pickup',
            'items' => [
                ['quantity' => 1, 'unit_price' => 15.00],
            ],
            'client_rates' => [
                'shipping_amount' => 4.99,
            ],
        ];

        $response = $this->postJson(route('api.v1.totals.deterministic'), $payload);

        // A mismatched client echo should trigger a 409 conflict, protecting the deterministic totals.
        $response->assertStatus(409)
            ->assertJsonPath('error', 'rate_tampering_detected')
            ->assertJsonPath('field', 'client_rates.shipping_amount');
    }

    /**
     * @return array{Country, string, string}
     */
    private function prepareDestination(string $regionCode, string $postalCode, float $taxRate): array
    {
        $country = Country::factory()->create([
            'cca2' => 'LT',
            'currency_code' => 'EUR',
            'vat_rate' => $taxRate,
        ]);

        $regionId = DB::table('regions')->insertGetId([
            'name' => 'Region '.$regionCode,
            'slug' => Str::slug('region-'.$regionCode),
            'code' => $regionCode,
            'description' => null,
            'is_enabled' => true,
            'is_default' => false,
            'country_id' => $country->getKey(),
            'zone_id' => null,
            'parent_id' => null,
            'level' => 1,
            'sort_order' => 1,
            'metadata' => json_encode(['tax_rate' => $taxRate]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        City::factory()->create([
            'country_id' => $country->getKey(),
            'region_id' => $regionId,
            'postal_codes' => [$postalCode],
        ]);

        return [$country, $regionCode, $postalCode];
    }
}
