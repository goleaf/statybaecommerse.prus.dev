<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\ContactMessageSubmitted;
use App\Models\ContactMessage;
use App\Models\SystemSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class SendContactMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    public function handle(): void
    {
        $recipient = SystemSetting::getPublic('mail.support_email', config('mail.from.address'));

        if (empty($recipient)) {
            Log::warning('Contact message could not be emailed because no recipient was configured.', [
                'contact_message_id' => $this->contactMessage->id,
            ]);

            return;
        }

        Mail::to($recipient)->send(new ContactMessageSubmitted($this->contactMessage));
    }
}
