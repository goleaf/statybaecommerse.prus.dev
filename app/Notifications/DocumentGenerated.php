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
use Illuminate\Support\Facades\Storage;

/**
 * DocumentGenerated
 *
 * Notification class for DocumentGenerated user notifications with multi-channel delivery and customizable content.
 */
final class DocumentGenerated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(
        private readonly Document $document,
        private readonly bool $attachPdf = true,
    ) {
        // Capture the generated document so it can be surfaced across channels.
    }

    /**
     * Handle via functionality with proper error handling.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        // Notify the recipient by email and through the database inbox.
        return ['mail', 'database'];
    }

    /**
     * Handle toMail functionality with proper error handling.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Resolve the locale up front so the template respects user preferences.
        $locale = method_exists($notifiable, 'preferredLocale')
            ? ($notifiable->preferredLocale() ?: app()->getLocale())
            : app()->getLocale();

        // Use a graceful fallback for the greeting name so queued jobs never fail.
        $displayName = $this->resolveNotifiableName($notifiable);

        $message = (new MailMessage())
            ->subject(__('documents.email.subject', ['title' => $this->document->title], $locale))
            ->greeting(__('documents.email.greeting', ['name' => $displayName], $locale))
            ->line(
                __('documents.email.generated', [
                    'title' => $this->document->title,
                    'type' => __('documents.types.' . $this->document->template->type, [], $locale),
                ], $locale)
            )
            ->line(
                __('documents.email.details', [
                    'date' => $this->document->generated_at?->format('Y-m-d H:i'),
                    'status' => __('documents.statuses.' . $this->document->status, [], $locale),
                ], $locale)
            );

        // Add a quick access action when the recipient is authorised to view the document.
        if ($this->canViewDocument($notifiable)) {
            $message->action(
                __('documents.email.view_document', [], $locale),
                route('filament.admin.resources.documents.view', $this->document),
            );
        }

        // Attach the freshly generated PDF when it is available in secure storage.
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

        // Always end with a small footer reminder.
        return $message->line(__('documents.email.footer', [], $locale));
    }

    /**
     * Convert the instance to an array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Persist a lean payload that powers the notification centre and API responses.
        return [
            'document_id' => $this->document->id,
            'document_title' => $this->document->title,
            'document_type' => $this->document->template->type,
            'document_status' => $this->document->status,
            'generated_at' => $this->document->generated_at?->toIso8601String(),
            'message' => __('documents.notification.generated', ['title' => $this->document->title]),
        ];
    }

    /**
     * Determine whether the notification should include the PDF attachment.
     */
    private function shouldAttachPdf(): bool
    {
        // Only attach PDFs when explicitly requested and the file path is set.
        return $this->attachPdf && $this->document->isPdf() && filled($this->document->file_path);
    }

    /**
     * Decide if the notifiable can access the document view link.
     */
    private function canViewDocument(object $notifiable): bool
    {
        // Use Gate to avoid relying on the global auth helper inside queued notifications.
        if (method_exists($notifiable, 'getAuthIdentifier')) {
            return Gate::forUser($notifiable)->allows('view', $this->document);
        }

        return method_exists($notifiable, 'can') && $notifiable->can('view', $this->document);
    }

    /**
     * Resolve a friendly display name for greeting lines.
     */
    private function resolveNotifiableName(object $notifiable): string
    {
        // Fall back to the application name so anonymous notifiables are handled gracefully.
        return (string) ($notifiable->name ?? $notifiable->email ?? config('app.name'));
    }
}
