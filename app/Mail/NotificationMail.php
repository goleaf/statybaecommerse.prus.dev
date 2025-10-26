<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Notification;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

/**
 * NotificationMail
 *
 * Mailable class for NotificationMail email sending with template management and attachment support.
 */
final class NotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Store the resolved locale so the view and subject stay in sync.
     */
    private ?string $cachedLocale = null;

    /**
     * Cache the normalized payload shared with the Blade template.
     *
     * @var array{
     *     notification: Notification,
     *     title: string,
     *     message: string,
     *     type: string,
     *     urgent: bool,
     *     color: string,
     *     tags: list<string>,
     *     created_at: DateTimeInterface,
     * }|null
     */
    private ?array $cachedViewData = null;

    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(public Notification $notification) {}

    /**
     * Handle envelope functionality with proper error handling.
     */
    public function envelope(): Envelope
    {
        $locale = $this->resolveLocale();
        $this->locale($locale); // Keep Markdown components translated consistently.

        return new Envelope(subject: $this->formatSubject($locale));
    }

    /**
     * Handle content functionality with proper error handling.
     */
    public function content(): Content
    {
        $locale = $this->resolveLocale();

        return new Content(
            view: 'emails.notification',
            with: $this->prepareViewData($locale)
        );
    }

    /**
     * Handle attachments functionality with proper error handling.
     */
    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        // Notification digests currently only render HTML; keep this stub for future asset support.
        return [];
    }

    private function resolveLocale(): string
    {
        // Allow notification payloads to override the locale while defaulting to the app setting.
        if ($this->cachedLocale !== null) {
            return $this->cachedLocale;
        }

        $payloadLocale = Arr::get($this->notification->data ?? [], 'locale');

        if (is_string($payloadLocale) && $payloadLocale !== '') {
            return $this->cachedLocale = $payloadLocale;
        }

        return $this->cachedLocale = app()->getLocale();
    }

    private function formatSubject(string $locale): string
    {
        $data = $this->prepareViewData($locale);
        $subject = __('mail.notification_subject', ['title' => $data['title']], $locale);

        if ($data['urgent'] === true) {
            $prefix = __('mail.notification_subject_urgent_prefix', [], $locale);

            return sprintf('[%s] %s', mb_strtoupper($prefix), $subject);
        }

        return $subject;
    }

    /**
     * Normalize the payload consumed by the Blade template.
     *
     * @return array<string, mixed>
     */
    /**
     * @return array{
     *     notification: Notification,
     *     title: string,
     *     message: string,
     *     type: string,
     *     urgent: bool,
     *     color: string,
     *     tags: list<string>,
     *     created_at: DateTimeInterface,
     * }
     */
    private function prepareViewData(string $locale): array
    {
        if ($this->cachedViewData !== null) {
            return $this->cachedViewData;
        }

        $raw = $this->notification->data ?? [];
        $title = $this->normalizeString($raw['title'] ?? null, __('mail.notification_default_title', [], $locale));
        $message = $this->normalizeString(
            $raw['message'] ?? null,
            $this->normalizeString($raw['body'] ?? null, __('mail.notification_default_body', [], $locale))
        );
        $type = $this->normalizeString($raw['type'] ?? null, 'general');
        $urgent = (bool) ($raw['urgent'] ?? false);
        $color = $this->normalizeColor($raw['color'] ?? null);
        $tags = $this->normalizeTags($raw['tags'] ?? []);

        $createdAt = $this->notification->created_at;
        if (! $createdAt instanceof DateTimeInterface) {
            // When notifications are previewed without persistence we still want a timestamp.
            $createdAt = now();
        }

        return $this->cachedViewData = [
            'notification' => $this->notification,
            'title'        => $title,
            'message'      => $message,
            'type'         => $type,
            'urgent'       => $urgent,
            'color'        => $color,
            'tags'         => $tags,
            'created_at'   => $createdAt,
        ];
    }

    private function normalizeString(mixed $value, string $fallback): string
    {
        if (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        return $fallback;
    }

    /**
     * @return list<string>
     */
    private function normalizeTags(mixed $value): array
    {
        $wrapped = Arr::wrap($value);

        $tags = array_filter(
            $wrapped,
            static fn ($tag): bool => is_string($tag) && trim($tag) !== ''
        );

        /** @var list<string> $normalized */
        $normalized = array_values(array_map(static fn (string $tag): string => trim($tag), $tags));

        return $normalized;
    }

    private function normalizeColor(mixed $value): string
    {
        if (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        return '#3B82F6';
    }
}
