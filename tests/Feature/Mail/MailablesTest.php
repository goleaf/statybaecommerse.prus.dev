<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Mail\Auth\PasswordResetMail;
use App\Mail\Auth\VerifyEmailMail;
use App\Mail\ContactMessageSubmitted;
use App\Mail\NotificationMail;
use App\Models\ContactMessage;
use App\Models\Notification;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Tests\TestCase;

final class MailablesTest extends TestCase
{
    public function test_notification_mail_formats_subject_and_content(): void
    {
        // Keep the locale deterministic so assertions remain stable across environments.
        $originalLocale = app()->getLocale();
        app()->setLocale('en');

        $notification = Notification::factory()->make([
            'data' => [
                'title'   => 'System Update',
                'message' => 'Service window at 02:00.',
                'type'    => 'system',
                'urgent'  => true,
                'tags'    => ['maintenance', '', null],
                'color'   => 'orange',
                'locale'  => 'en',
            ],
            'created_at' => CarbonImmutable::now(),
        ]);

        $mail = new NotificationMail($notification);

        $envelope = $mail->envelope();
        $this->assertSame('[URGENT] System Update', $envelope->subject);

        $content = $mail->content();
        $this->assertSame('emails.notification', $content->view);
        $this->assertSame('System Update', $content->with['title']);
        $this->assertSame('Service window at 02:00.', $content->with['message']);
        $this->assertSame('system', $content->with['type']);
        $this->assertTrue($content->with['urgent']);
        $this->assertSame(['maintenance'], $content->with['tags']);
        $this->assertSame('orange', $content->with['color']);
        $this->assertInstanceOf(DateTimeInterface::class, $content->with['created_at']);

        app()->setLocale($originalLocale);
    }

    public function test_notification_mail_falls_back_to_body_key_and_locale(): void
    {
        $originalLocale = app()->getLocale();
        app()->setLocale('lt');

        $notification = Notification::factory()->make([
            'data' => [
                'title' => 'Svarbus pranešimas',
                'body'  => 'Turinys iš sistemos.',
                'type'  => 'system',
            ],
            'created_at' => CarbonImmutable::now(),
        ]);

        $mail = new NotificationMail($notification);

        $envelope = $mail->envelope();
        $this->assertSame('Svarbus pranešimas', $envelope->subject);

        $content = $mail->content();
        $this->assertSame('Turinys iš sistemos.', $content->with['message']);

        // Restore the previous locale to avoid bleeding into other tests.
        app()->setLocale($originalLocale);
    }

    public function test_password_reset_mail_uses_preferred_locale_and_payload(): void
    {
        $mail = new PasswordResetMail('https://example.test/reset', 30, 'en');

        $envelope = $mail->envelope();
        $this->assertSame('Reset Password', $envelope->subject);

        $content = $mail->content();
        $this->assertSame('emails.auth.password-reset', $content->markdown);
        $this->assertSame('https://example.test/reset', $content->with['url']);
        $this->assertSame(30, $content->with['minutes']);
        $this->assertSame([], $mail->attachments());
    }

    public function test_verify_email_mail_uses_preferred_locale(): void
    {
        $mail = new VerifyEmailMail('https://example.test/verify', 'lt');

        $envelope = $mail->envelope();
        $this->assertSame('Patvirtinkite el. paštą', $envelope->subject);

        $content = $mail->content();
        $this->assertSame('emails.auth.verify', $content->markdown);
        $this->assertSame('https://example.test/verify', $content->with['url']);
        $this->assertSame([], $mail->attachments());
    }

    public function test_contact_message_mail_uses_subject_fallback_when_empty(): void
    {
        $originalLocale = app()->getLocale();
        app()->setLocale('en');

        $message = ContactMessage::factory()->make(['subject' => '']);
        $mail = new ContactMessageSubmitted($message);

        $envelope = $mail->envelope();
        $this->assertSame('New contact message: New enquiry', $envelope->subject);

        $content = $mail->content();
        $this->assertSame('emails.contact.submitted', $content->view);
        $this->assertSame($message, $content->with['contactMessage']);

        app()->setLocale($originalLocale);
    }
}
