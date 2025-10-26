<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MailPreviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_available_mail_previews_in_requested_locale(): void
    {
        // Create a user so the authenticated middleware passes and the preview route is accessible.
        $user = User::factory()->create();

        // Force a deterministic locale configuration to verify preview listings respect translations.
        config(['app.supported_locales' => 'lt,en']);

        // Hit the index route with the Lithuanian locale to ensure filtering happens correctly.
        $response = $this->actingAs($user)->get(route('mail-previews.index', ['locale' => 'lt']));

        // Assert the route renders successfully with the expected view.
        $response->assertOk()->assertViewIs('mail.previews.index');

        // Confirm the controller surfaces translated labels and locale metadata in the payload.
        $response->assertViewHas('previews', static function (array $previews): bool {
            $labels = array_column($previews, 'label');

            return in_array('Užsakymas patvirtintas', $labels, true)
                && in_array('Slaptažodžio atkūrimas', $labels, true)
                && count($previews) >= 3;
        });
        $response->assertViewHas('selectedLocale', 'lt');
        $response->assertViewHas('availableLocales', ['lt', 'en']);
    }

    public function test_show_renders_password_reset_mail_with_translated_subject_header(): void
    {
        // Authenticate a user so the preview endpoint is reachable under the auth middleware.
        $user = User::factory()->create();

        // Request the password reset preview using a Lithuanian locale to validate translation wiring.
        $response = $this->actingAs($user)->get(route('mail-previews.show', [
            'mail'   => 'password-reset',
            'locale' => 'lt',
        ]));

        // Ensure the response is successful and reports the expected metadata headers.
        $response->assertOk();
        $response->assertHeader('X-Mail-Preview', 'password-reset');
        $response->assertHeader('X-Mail-Locale', 'lt');
        $response->assertHeader('X-Mail-Subject', 'Slaptažodžio atkūrimas');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');

        // The rendered HTML should include the translated title so humans can verify the output quickly.
        $response->assertSee('Slaptažodžio atkūrimas', false);
    }

    public function test_show_returns_not_found_for_unknown_preview_slug(): void
    {
        // Authenticate a user to satisfy the middleware chain guarding the preview routes.
        $user = User::factory()->create();

        // Attempt to render a non-existent preview to confirm error handling remains strict.
        $response = $this->actingAs($user)->get(route('mail-previews.show', ['mail' => 'does-not-exist']));

        // Expect a 404 so invalid slugs do not disclose unintended data or crash the controller.
        $response->assertNotFound();
    }
}
