<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Exceptions\Domain\InventoryUnavailableException;
use App\Exceptions\Domain\OrderNotFoundException;
use App\Services\TranslationService;
use App\Support\ErrorCode;
use Illuminate\Support\Facades\Route;

final class DomainExceptionResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/testing/domain-exception', function () {
            throw new OrderNotFoundException('ORD-123');
        });

        Route::get('/testing/inventory-exception', function () {
            throw new InventoryUnavailableException('SKU-42');
        });
    }

    public function test_domain_exception_response_contains_expected_payload(): void
    {
        $defaultLocale = TranslationService::getDefaultLocale();

        $response = $this->getJson('/testing/domain-exception');

        $response
            ->assertStatus(404)
            ->assertJsonStructure([
                'code',
                'message',
                'details' => [
                    'locale',
                    'context' => ['order'],
                ],
                'trace_id',
            ])
            ->assertJsonPath('code', ErrorCode::OrderNotFound->value)
            ->assertJsonPath('message', TranslationService::get('exceptions.orders.not_found', ['order' => 'ORD-123'], $defaultLocale))
            ->assertJsonPath('details.locale', $defaultLocale)
            ->assertJsonPath('details.context.order', 'ORD-123');

        $traceId = $response->json('trace_id');
        $this->assertIsString($traceId);
        $this->assertNotEmpty($traceId);
        $this->assertSame($traceId, $response->headers->get('X-Correlation-ID'));
        $this->assertSame($defaultLocale, $response->headers->get('Content-Language'));
    }

    public function test_domain_exception_payload_varies_per_exception(): void
    {
        $defaultLocale = TranslationService::getDefaultLocale();

        $response = $this->getJson('/testing/inventory-exception');

        $response
            ->assertStatus(409)
            ->assertJsonPath('code', ErrorCode::InventoryInsufficient->value)
            ->assertJsonPath('message', TranslationService::get('exceptions.inventory.insufficient', ['sku' => 'SKU-42'], $defaultLocale))
            ->assertJsonPath('details.context.sku', 'SKU-42');
    }

    public function test_accept_language_header_changes_localized_message(): void
    {
        $responseEn = $this->withHeader('Accept-Language', 'en')->getJson('/testing/domain-exception');
        $responseEn
            ->assertStatus(404)
            ->assertJsonPath('message', TranslationService::get('exceptions.orders.not_found', ['order' => 'ORD-123'], 'en'))
            ->assertJsonPath('details.locale', 'en');

        $responseDe = $this->withHeader('Accept-Language', 'de')->getJson('/testing/domain-exception');
        $responseDe
            ->assertStatus(404)
            ->assertJsonPath('message', TranslationService::get('exceptions.orders.not_found', ['order' => 'ORD-123'], 'de'))
            ->assertJsonPath('details.locale', 'de');
    }

    public function test_unsupported_locale_falls_back_to_default(): void
    {
        $defaultLocale = TranslationService::getDefaultLocale();

        $response = $this->withHeader('Accept-Language', 'fr')->getJson('/testing/domain-exception');

        $response
            ->assertStatus(404)
            ->assertJsonPath('message', TranslationService::get('exceptions.orders.not_found', ['order' => 'ORD-123'], $defaultLocale))
            ->assertJsonPath('details.locale', $defaultLocale);
    }
}
