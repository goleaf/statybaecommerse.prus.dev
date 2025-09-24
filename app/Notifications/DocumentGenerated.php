<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

/**
 * DocumentGenerated
 *
 * Notification class for DocumentGenerated user notifications with multi-channel delivery and customizable content.
 */
final class DocumentGenerated extends Notification
{
    use Queueable;

    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(private readonly Document $document, private readonly bool $attachPdf = true) {}

    /**
     * Handle via functionality with proper error handling.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Handle toMail functionality with proper error handling.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $locale = method_exists($notifiable, 'preferredLocale') ? $notifiable->preferredLocale() ?: app()->getLocale() : app()->getLocale();
        $message = (new MailMessage)->subject(__('documents.email.subject', ['title' => $this->document->title], $locale))->greeting(__('documents.email.greeting', ['name' => $notifiable->name], $locale))->line(__('documents.email.generated', ['title' => $this->document->title, 'type' => __('documents.types.'.$this->document->template->type, [], $locale)], $locale))->line(__('documents.email.details', ['date' => $this->document->generated_at?->format('Y-m-d H:i'), 'status' => __('documents.statuses.'.$this->document->status, [], $locale)], $locale));
        // Add view action if user has access
        if (auth()->user()?->can('view', $this->document)) {
            $message->action(__('documents.email.view_document', [], $locale), route('filament.admin.resources.documents.view', $this->document));
        }
        // Attach PDF if available and requested
        if ($this->attachPdf && $this->document->isPdf() && $this->document->file_path) {
            if (Storage::disk('public')->exists($this->document->file_path)) {
                $message->attach(Attachment::fromStorageDisk('public', $this->document->file_path)->as($this->document->title.'.pdf')->withMime('application/pdf'));
            }
        }
        $message->line(__('documents.email.footer', [], $locale));

        return $message;
    }

    /**
     * Convert the instance to an array representation.
     */
    public function toArray(object $notifiable): array
    {
        return ['document_id' => $this->document->id, 'document_title' => $this->document->title, 'document_type' => $this->document->template->type, 'document_status' => $this->document->status, 'generated_at' => $this->document->generated_at, 'message' => __('documents.notification.generated', ['title' => $this->document->title])];
    }
}
