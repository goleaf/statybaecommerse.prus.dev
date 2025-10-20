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
            ->assertJsonStructure([
                'error' => ['code', 'message', 'status'],
                'meta' => ['correlation_id', 'locale'],
            ])
            ->assertJson([
                'error' => [
                    'code' => ErrorCodes::ORDER_NOT_FOUND,
                    'status' => 404,
                ],
                'meta' => [
                    'locale' => $defaultLocale,
                ],
            ]);

        $expectedMessage = TranslationService::get(
            ErrorCodes::translationKey(ErrorCodes::ORDER_NOT_FOUND),
            ['order' => 'ORD-123'],
            $defaultLocale
        );
        $this->assertSame($expectedMessage, $response->json('error.message'));

        $correlationId = $response->json('meta.correlation_id');
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
            ->assertJsonStructure([
                'error' => ['code', 'message', 'status'],
                'meta' => ['correlation_id', 'locale'],
            ])
            ->assertJson([
                'error' => [
                    'code' => ErrorCodes::INVENTORY_INSUFFICIENT,
                    'status' => 409,
                ],
            ]);

        $expectedMessage = TranslationService::get(
            ErrorCodes::translationKey(ErrorCodes::INVENTORY_INSUFFICIENT),
            ['sku' => 'SKU-42'],
            $defaultLocale
        );
        $this->assertSame($expectedMessage, $response->json('error.message'));
    }

    public function test_accept_language_header_changes_localized_message(): void
    {
        $responseEn = $this->withHeader('Accept-Language', 'en')->getJson('/testing/domain-exception');
        $responseEn
            ->assertStatus(404)
            ->assertJson([
                'error' => [
                    'code' => ErrorCodes::ORDER_NOT_FOUND,
                    'status' => 404,
                    'message' => TranslationService::get(
                        ErrorCodes::translationKey(ErrorCodes::ORDER_NOT_FOUND),
                        ['order' => 'ORD-123'],
                        'en'
                    ),
                ],
                'meta' => [
                    'locale' => 'en',
                ],
            ]);

        $responseDe = $this->withHeader('Accept-Language', 'de')->getJson('/testing/domain-exception');
        $responseDe
            ->assertStatus(404)
            ->assertJson([
                'error' => [
                    'code' => ErrorCodes::ORDER_NOT_FOUND,
                    'status' => 404,
                    'message' => TranslationService::get(
                        ErrorCodes::translationKey(ErrorCodes::ORDER_NOT_FOUND),
                        ['order' => 'ORD-123'],
                        'de'
                    ),
                ],
                'meta' => [
                    'locale' => 'de',
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
                    'code' => ErrorCodes::ORDER_NOT_FOUND,
                    'status' => 404,
                    'message' => TranslationService::get(
                        ErrorCodes::translationKey(ErrorCodes::ORDER_NOT_FOUND),
                        ['order' => 'ORD-123'],
                        $defaultLocale
                    ),
                ],
                'meta' => [
                    'locale' => $defaultLocale,
                ],
            ]);
    }
}
