<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\Auth\PasswordResetMail;
use App\Mail\Auth\VerifyEmailMail;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Str;

use function in_array;

final class MailPreviewController
{
    public function index(Request $request): View
    {
        $locale = $this->resolveLocale($request);
        $definitions = $this->availableMailables();

        $previews = [];

        foreach ($definitions as $slug => $definition) {
            $previews[] = [
                'slug'  => $slug,
                'label' => $definition['label']($locale),
            ];
        }

        return view('mail.previews.index', [
            'previews'         => $previews,
            'selectedLocale'   => $locale,
            'availableLocales' => $this->previewLocales(),
        ]);
    }

    public function show(Request $request, string $mail): Response
    {
        $locale = $this->resolveLocale($request);
        $definitions = $this->availableMailables();

        abort_unless(isset($definitions[$mail]), 404);

        /** @var callable(string): Mailable $factory */
        $factory = $definitions[$mail]['make'];
        $mailable = $factory($locale);

        $preview = $this->renderMailablePreview($mailable, $locale);

        return new Response($preview['html'], 200, array_filter([
            // Expose the rendered markup with a deterministic content type for browser previews.
            'Content-Type' => 'text/html; charset=UTF-8',
            // Surface the mailable identifier so automated tooling can assert which template rendered.
            'X-Mail-Preview' => $mail,
            // Let callers verify the locale that ended up rendering the template.
            'X-Mail-Locale' => $locale,
            // Share the computed subject line when available so subject regressions are easy to spot.
            'X-Mail-Subject' => $preview['subject'],
        ], static fn (?string $value): bool => $value !== null));
    }

    /**
     * @return array<string, array{label: callable(string): string, make: callable(string): Mailable}>
     */
    private function availableMailables(): array
    {
        return [
            'order-confirmation' => [
                'label' => static fn (string $locale): string => __('mail.order_confirmation_title', [], $locale),
                'make'  => function (string $locale): Mailable {
                    $order = $this->fakeOrder($locale);

                    return new OrderConfirmationMail($order);
                },
            ],
            'password-reset' => [
                'label' => static fn (string $locale): string => __('mail.reset_password_title', [], $locale),
                'make'  => function (string $locale): Mailable {
                    $passwordBroker = config('auth.defaults.passwords', 'users');
                    $passwordBroker = is_string($passwordBroker) && $passwordBroker !== '' ? $passwordBroker : 'users';
                    $expireConfig = config("auth.passwords.{$passwordBroker}.expire", 60);
                    $minutes = is_int($expireConfig) ? $expireConfig : (is_numeric($expireConfig) ? (int) $expireConfig : 60);
                    $url = url('reset-password/' . Str::random(32) . '?email=' . urlencode('customer@example.test'));

                    return new PasswordResetMail($url, $minutes, $locale);
                },
            ],
            'verify-email' => [
                'label' => static fn (string $locale): string => __('mail.verify_email_title', [], $locale),
                'make'  => function (string $locale): Mailable {
                    $url = url('verify-email/' . Str::random(32) . '/' . Str::random(64));

                    return new VerifyEmailMail($url, $locale);
                },
            ],
        ];
    }

    private function resolveLocale(Request $request): string
    {
        $locales = $this->previewLocales();
        $requested = (string) $request->query('locale', app()->getLocale());

        if ($requested !== '' && in_array($requested, $locales, true)) {
            return $requested;
        }

        return $locales[0] ?? app()->getLocale();
    }

    /**
     * @return list<string>
     */
    private function previewLocales(): array
    {
        $configuredRaw = config('app.supported_locales', 'en');
        $configured = is_string($configuredRaw) && $configuredRaw !== '' ? $configuredRaw : 'en';
        $locales = array_filter(array_map(trim(...), explode(',', $configured)));

        if ($locales === []) {
            $locales = [app()->getLocale()];
        }

        return array_values(array_unique($locales));
    }

    /**
     * Render the given mailable for preview purposes while keeping locale handling deterministic.
     *
     * @return array{html: string, subject: string|null}
     */
    private function renderMailablePreview(Mailable $mailable, string $locale): array
    {
        // Capture the original locale so we can safely restore it once rendering completes.
        $previousLocale = app()->getLocale();
        $html = '';
        $subject = null;

        try {
            // Only switch locales when needed to keep render output predictable and isolated per preview.
            if ($locale !== '' && $locale !== $previousLocale) {
                app()->setLocale($locale);
            }

            // Render the mailable immediately so Markdown/Blade translations respect the requested locale.
            $html = $mailable->render();
            // Resolve the subject after rendering because envelope-based mailables populate it lazily.
            $subject = $this->resolveMailableSubject($mailable);
        } finally {
            // Ensure the global locale is always restored to avoid bleeding into subsequent previews or requests.
            if ($locale !== '' && $locale !== $previousLocale) {
                app()->setLocale($previousLocale);
            }
        }

        return [
            'html'    => $html,
            'subject' => $subject,
        ];
    }

    /**
     * Derive the most accurate subject line for the preview, supporting both legacy and modern mailables.
     */
    private function resolveMailableSubject(Mailable $mailable): ?string
    {
        // Prefer explicitly assigned subjects to honour legacy build() style mailables.
        /** @var string|null $subjectProperty */
        $subjectProperty = $mailable->subject;

        if ($subjectProperty !== null && $subjectProperty !== '') {
            return $subjectProperty;
        }

        // Fall back to the envelope contract so envelope()/subject() style mailables are supported too.
        if (method_exists($mailable, 'envelope')) {
            /** @var Envelope $envelope */
            $envelope = $mailable->envelope();
            $subject = $envelope->subject;

            if (is_string($subject) && $subject !== '') {
                return $subject;
            }
        }

        // Return null when no subject could be resolved, keeping headers free of misleading data.
        return null;
    }

    private function fakeOrder(string $locale): Order
    {
        $order = new Order;
        $order->setAttribute('number', 'ORD-123456');
        $order->setAttribute('grand_total_amount', 149.99);
        $order->setAttribute('currency_code', 'EUR');
        $order->setAttribute('status', 'processing');
        $order->setAttribute('locale', $locale);
        $order->setAttribute('created_at', now());
        $order->setAttribute('updated_at', now());
        $order->setRelation('user', $this->fakeUser());

        return $order;
    }

    private function fakeUser(): User
    {
        $user = new User;
        $user->name = 'Austėja Vaitkūnaitė';
        $user->email = 'customer@example.test';
        $user->preferred_locale = 'lt';

        return $user;
    }
}
