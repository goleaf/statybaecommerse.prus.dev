<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Legal;
use App\Models\Translations\LegalTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\TestCase;

final class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('legalRouteProvider')] // Using attributes silences PHPUnit's metadata deprecation warnings.
    public function test_legal_pages_render_successfully(string $routeName, array $state, string $expectedTitle): void
    {
        Legal::factory()
            ->has(
                LegalTranslation::factory()
                    ->english()
                    ->state([
                        'title' => $expectedTitle,
                        'content' => '<p>Test content for '.$expectedTitle.'</p>',
                        'seo_title' => $expectedTitle,
                        'seo_description' => 'SEO description for '.$expectedTitle,
                    ]),
                'translations'
            )
            ->create([
                'key' => $state['key'],
                'type' => $state['type'],
                'is_enabled' => true,
                'is_required' => $state['is_required'] ?? false,
                'published_at' => now(),
            ]);

        $this
            ->get(route($routeName))
            ->assertOk()
            ->assertSee($expectedTitle);
    }

    public function test_legal_page_gracefully_handles_missing_document(): void
    {
        $this
            ->get(route('frontend.legal.privacy'))
            ->assertOk()
            ->assertSee('Our privacy policy is currently unavailable.');
    }

    /**
     * @return array<string, array{routeName: string, state: array<string, mixed>, expectedTitle: string}>
     */
    public static function legalRouteProvider(): array
    {
        return [
            'privacy page' => [
                'routeName' => 'frontend.legal.privacy',
                'state' => [
                    'key' => 'privacy-policy',
                    'type' => 'privacy_policy',
                    'is_required' => true,
                ],
                'expectedTitle' => 'Privacy Policy',
            ],
            'terms page' => [
                'routeName' => 'frontend.legal.terms',
                'state' => [
                    'key' => 'terms-of-use',
                    'type' => 'terms_of_use',
                    'is_required' => true,
                ],
                'expectedTitle' => 'Terms & Conditions',
            ],
            'cookie policy page' => [
                'routeName' => 'frontend.legal.cookies',
                'state' => [
                    'key' => 'cookie-policy',
                    'type' => 'cookie_policy',
                ],
                'expectedTitle' => 'Cookie Policy',
            ],
            'return policy page' => [
                'routeName' => 'frontend.legal.returns',
                'state' => [
                    'key' => 'return-policy',
                    'type' => 'refund_policy',
                ],
                'expectedTitle' => 'Return & Refund Policy',
            ],
        ];
    }
}
