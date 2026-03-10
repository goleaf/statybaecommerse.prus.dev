<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Legal;
use App\Models\Translations\LegalTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\TestCase;

final class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.locale', 'lt');
        config()->set('app.fallback_locale', 'lt');
        app()->setLocale('lt');
    }

    #[DataProvider('legalRouteProvider')] // Using attributes silences PHPUnit's metadata deprecation warnings.
    public function test_legal_pages_render_successfully(string $routeName, array $state, string $expectedTitle): void
    {
        Legal::factory()
            ->has(
                LegalTranslation::factory()
                    ->lithuanian()
                    ->state([
                        'title'           => $expectedTitle,
                        'content'         => '<p>Test content for ' . $expectedTitle . '</p>',
                        'seo_title'       => $expectedTitle,
                        'seo_description' => 'SEO description for ' . $expectedTitle,
                    ]),
                'translations'
            )
            ->create([
                'key'          => $state['key'],
                'type'         => $state['type'],
                'is_enabled'   => true,
                'is_required'  => $state['is_required'] ?? false,
                'published_at' => now(),
            ]);

        $this
            ->get(route($routeName))
            ->assertOk()
            ->assertSee($expectedTitle);
    }

    public function test_legal_page_gracefully_handles_missing_document(): void
    {
        $expectedTitle = (string) trans('info_pages.pages.privacy.title', [], 'lt');
        $expectedDescription = (string) trans('info_pages.pages.privacy.description', [], 'lt');

        $this
            ->get(route('localized.legal.privacy'))
            ->assertOk()
            ->assertSeeText($expectedTitle)
            ->assertSeeText($expectedDescription);
    }

    #[DataProvider('mojibakeLegalRouteProvider')]
    public function test_footer_legal_pages_repair_mojibake_content(
        string $routeName,
        array $state,
        string $expectedTitle,
        string $expectedBody
    ): void {
        config()->set('app.locale', 'lt');
        config()->set('app.fallback_locale', 'lt');
        app()->setLocale('lt');

        $legal = Legal::factory()->create([
            'key'          => $state['key'],
            'type'         => $state['type'],
            'is_enabled'   => true,
            'is_required'  => $state['is_required'] ?? false,
            'published_at' => now(),
        ]);

        $translation = LegalTranslation::factory()->create([
            'legal_id' => $legal->id,
            'locale'   => 'lt',
            'title'    => 'Laikinas pavadinimas',
            'content'  => '<p>Laikinas tekstas</p>',
        ]);

        $brokenTitle = mb_convert_encoding($expectedTitle, 'UTF-8', 'Windows-1252');
        $brokenBody = mb_convert_encoding($expectedBody, 'UTF-8', 'Windows-1252');

        DB::table('legal_translations')
            ->where('id', $translation->id)
            ->update([
                'title'   => $brokenTitle,
                'content' => '<p>' . $brokenBody . '</p>',
            ]);

        $response = $this
            ->get(route($routeName))
            ->assertOk()
            ->assertSeeText($expectedTitle)
            ->assertSeeText($expectedBody);

        if ($brokenTitle !== $expectedTitle) {
            $response->assertDontSeeText($brokenTitle);
        }

        if ($brokenBody !== $expectedBody) {
            $response->assertDontSeeText($brokenBody);
        }
    }

    /**
     * @return array<string, array{routeName: string, state: array<string, mixed>, expectedTitle: string}>
     */
    public static function legalRouteProvider(): array
    {
        return [
            'privacy page' => [
                'routeName' => 'localized.legal.privacy',
                'state'     => [
                    'key'         => 'privacy-policy',
                    'type'        => 'privacy_policy',
                    'is_required' => true,
                ],
                'expectedTitle' => 'Privatumo politika',
            ],
            'terms page' => [
                'routeName' => 'localized.legal.terms',
                'state'     => [
                    'key'         => 'terms-of-use',
                    'type'        => 'terms_of_use',
                    'is_required' => true,
                ],
                'expectedTitle' => 'Naudojimosi sąlygos',
            ],
            'cookie policy page' => [
                'routeName' => 'localized.legal.cookies',
                'state'     => [
                    'key'  => 'cookie-policy',
                    'type' => 'cookie_policy',
                ],
                'expectedTitle' => 'Slapukų politika',
            ],
            'return policy page' => [
                'routeName' => 'localized.legal.returns',
                'state'     => [
                    'key'  => 'return-policy',
                    'type' => 'refund_policy',
                ],
                'expectedTitle' => 'Grąžinimo politika',
            ],
        ];
    }

    /**
     * @return array<string, array{routeName: string, state: array<string, mixed>, expectedTitle: string, expectedBody: string}>
     */
    public static function mojibakeLegalRouteProvider(): array
    {
        return [
            'privacy page repairs body copy' => [
                'routeName' => 'localized.legal.privacy',
                'state'     => [
                    'key'         => 'privacy-policy',
                    'type'        => 'privacy_policy',
                    'is_required' => true,
                ],
                'expectedTitle' => 'Privatumo politika',
                'expectedBody' => 'Ši privatumo politika paaiškina, kaip renkame, naudojame ir saugome jūsų asmens duomenis naudojantis mūsų svetaine bei paslaugomis.',
            ],
            'terms page repairs title and body' => [
                'routeName' => 'localized.legal.terms',
                'state'     => [
                    'key'         => 'terms-of-use',
                    'type'        => 'terms_of_use',
                    'is_required' => true,
                ],
                'expectedTitle' => 'Naudojimosi sąlygos',
                'expectedBody' => 'Naudodamiesi svetaine sutinkate su šiomis sąlygomis. Prieš pateikdami užsakymą, susipažinkite su visa sąlygų redakcija.',
            ],
            'cookie page repairs title and body' => [
                'routeName' => 'localized.legal.cookies',
                'state'     => [
                    'key'  => 'cookie-policy',
                    'type' => 'cookie_policy',
                ],
                'expectedTitle' => 'Slapukų politika',
                'expectedBody' => 'Slapukus naudojame svetainės funkcionalumui, analitikai ir turinio personalizavimui. Daugiau informacijos rasite šiame dokumente.',
            ],
        ];
    }
}
