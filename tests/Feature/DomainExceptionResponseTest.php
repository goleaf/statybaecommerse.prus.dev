<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Exceptions\Domain\InventoryUnavailableException;
use App\Exceptions\Domain\OrderNotFoundException;
use App\Services\TranslationService;
use App\Support\ErrorCodes;
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
            ->assertJsonStructure(['error' => ['code', 'message'], 'correlation_id'])
            ->assertJson([
                'error' => [
                    'code' => ErrorCodes::ORDER_NOT_FOUND,
                    'message' => TranslationService::get(
                        ErrorCodes::messageKey(ErrorCodes::ORDER_NOT_FOUND),
                        ['order' => 'ORD-123'],
                        $defaultLocale,
                    ),
                ],
            ]);

        $correlationId = $response->json('correlation_id');
        $this->assertIsString($correlationId);
        $this->assertNotEmpty($correlationId);
        $this->assertSame($correlationId, $response->headers->get('X-Correlation-ID'));
    }

    public function test_domain_exception_payload_varies_per_exception(): void
    {
        $defaultLocale = TranslationService::getDefaultLocale();

        $response = $this->getJson('/testing/inventory-exception');

        $response
            ->assertStatus(409)
            ->assertJson([
                'error' => [
                    'code' => ErrorCodes::INVENTORY_INSUFFICIENT,
                    'message' => TranslationService::get(
                        ErrorCodes::messageKey(ErrorCodes::INVENTORY_INSUFFICIENT),
                        ['sku' => 'SKU-42'],
                        $defaultLocale,
                    ),
                ],
            ])
            ->assertJsonStructure(['error' => ['code', 'message'], 'correlation_id']);
    }

    public function test_accept_language_header_changes_localized_message(): void
    {
        $responseEn = $this->withHeader('Accept-Language', 'en')->getJson('/testing/domain-exception');
        $responseEn
            ->assertStatus(404)
            ->assertJson([
                'error' => [
                    'message' => TranslationService::get(
                        ErrorCodes::messageKey(ErrorCodes::ORDER_NOT_FOUND),
                        ['order' => 'ORD-123'],
                        'en',
                    ),
                ],
            ]);

        $responseDe = $this->withHeader('Accept-Language', 'de')->getJson('/testing/domain-exception');
        $responseDe
            ->assertStatus(404)
            ->assertJson([
                'error' => [
                    'message' => TranslationService::get(
                        ErrorCodes::messageKey(ErrorCodes::ORDER_NOT_FOUND),
                        ['order' => 'ORD-123'],
                        'de',
                    ),
                ],
            ]);
    }

    public function test_unsupported_locale_falls_back_to_default(): void
    {
        $defaultLocale = TranslationService::getDefaultLocale();

        $response = $this->withHeader('Accept-Language', 'fr')->getJson('/testing/domain-exception');

        $response
            ->assertStatus(404)
            ->assertJson([
                'error' => [
                    'message' => TranslationService::get(
                        ErrorCodes::messageKey(ErrorCodes::ORDER_NOT_FOUND),
                        ['order' => 'ORD-123'],
                        $defaultLocale,
                    ),
                ],
            ]);
    }
}
