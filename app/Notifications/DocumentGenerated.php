<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Document;
use App\Support\Storage\SecureStorage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/**
 * DocumentGenerated
 *
 * Notification class for document generated events.
 */
final class DocumentGenerated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Document $document,
        private readonly bool $attachPdf = true,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = method_exists($notifiable, 'preferredLocale')
            ? ($notifiable->preferredLocale() ?: app()->getLocale())
            : app()->getLocale();

        $displayName = $this->resolveNotifiableName($notifiable);
        $generatedAt = $this->document->generated_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i');

        $message = (new MailMessage)
            ->subject(__('messages.documents', [], $locale))
            ->greeting(__('messages.documents', [], $locale) . ': ' . $displayName)
            ->line(__('messages.documents', [], $locale) . ': ' . (string) $this->document->title)
            ->line(__('documents.generated_on', [], $locale) . ': ' . $generatedAt)
            ->line(__('messages.status', [], $locale) . ': ' . (string) $this->document->status);

        if ($this->canViewDocument($notifiable) && Route::has('filament.admin.resources.documents.view')) {
            $message->action(
                __('messages.view', [], $locale),
                route('filament.admin.resources.documents.view', $this->document),
            );
        }

        if ($this->shouldAttachPdf()) {
            $disk = SecureStorage::disk();

            if (Storage::disk($disk)->exists($this->document->file_path)) {
                $message->attach(
                    Attachment::fromStorageDisk($disk, $this->document->file_path)
                        ->as($this->document->title . '.pdf')
                        ->withMime('application/pdf')
                );
            }
        }

        return $message;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'document_id'     => $this->document->id,
            'document_title'  => $this->document->title,
            'document_type'   => $this->document->template->type ?? null,
            'document_status' => $this->document->status,
            'generated_at'    => $this->document->generated_at?->toIso8601String(),
            'message'         => __('messages.documents', ['title' => $this->document->title]),
        ];
    }

    private function shouldAttachPdf(): bool
    {
        return $this->attachPdf && $this->document->isPdf() && filled($this->document->file_path);
    }

    private function canViewDocument(object $notifiable): bool
    {
        if (method_exists($notifiable, 'getAuthIdentifier')) {
            return Gate::forUser($notifiable)->allows('view', $this->document);
        }

        return method_exists($notifiable, 'can') && $notifiable->can('view', $this->document);
    }

    private function resolveNotifiableName(object $notifiable): string
    {
        return (string) ($notifiable->name ?? $notifiable->email ?? config('app.name'));
    }
}
