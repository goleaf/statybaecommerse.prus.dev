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

final class NotificationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    private ?string $cachedLocale = null;

    /**
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

    public function __construct(public Notification $notification) {}

    public function envelope(): Envelope
    {
        $locale = $this->resolveLocale();
        $this->locale($locale);

        return new Envelope(subject: $this->formatSubject($locale));
    }

    public function content(): Content
    {
        $locale = $this->resolveLocale();

        return new Content(
            view: 'emails.notification',
            with: $this->prepareViewData($locale),
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    private function resolveLocale(): string
    {
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
        $subject = $data['title'];

        if ($data['urgent'] === true) {
            return '[' . __('messages.notifications', [], $locale) . '] ' . $subject;
        }

        return $subject;
    }

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

        $raw = is_array($this->notification->data) ? $this->notification->data : [];

        $title = $this->normalizeString($raw['title'] ?? null, __('messages.mail', [], $locale));
        $message = $this->normalizeString($raw['message'] ?? null, __('messages.notifications', [], $locale));
        $type = $this->normalizeString($raw['type'] ?? null, 'general');
        $urgent = (bool) ($raw['urgent'] ?? false);
        $color = $this->normalizeColor($raw['color'] ?? null);
        $tags = $this->normalizeTags($raw['tags'] ?? []);

        $createdAt = $this->notification->created_at;
        if (! $createdAt instanceof DateTimeInterface) {
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
