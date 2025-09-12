<?php declare(strict_types=1);

use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

describe('AdminNotification', function () {
    it('can be instantiated with required parameters', function () {
        $notification = new AdminNotification(
            'Test Title',
            'Test Message',
            'info'
        );

        expect($notification->title)->toBe('Test Title');
        expect($notification->message)->toBe('Test Message');
        expect($notification->type)->toBe('info');
    });

    it('uses default type when not provided', function () {
        $notification = new AdminNotification(
            'Test Title',
            'Test Message'
        );

        expect($notification->type)->toBe('info');
    });

    it('implements ShouldQueue interface', function () {
        $notification = new AdminNotification('Test', 'Message');

        expect($notification)->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
    });

    it('uses correct notification channels', function () {
        $notification = new AdminNotification('Test', 'Message');
        $user = User::factory()->create();

        $channels = $notification->via($user);

        expect($channels)->toBe(['database', 'mail']);
    });

    it('generates correct mail message', function () {
        $notification = new AdminNotification(
            'Test Title',
            'Test Message',
            'warning'
        );
        $user = User::factory()->create();

        $mailMessage = $notification->toMail($user);

        expect($mailMessage)->toBeInstanceOf(MailMessage::class);
        expect($mailMessage->subject)->toBe('Test Title');
        expect($mailMessage->introLines)->toContain('Test Message');
    });

    it('generates correct database notification data', function () {
        $notification = new AdminNotification(
            'Test Title',
            'Test Message',
            'success'
        );
        $user = User::factory()->create();

        $data = $notification->toDatabase($user);

        expect($data)->toBeArray();
        expect($data['title'])->toBe('Test Title');
        expect($data['message'])->toBe('Test Message');
        expect($data['type'])->toBe('success');
        expect($data['sent_at'])->not()->toBeNull();
    });

    it('generates correct array notification data', function () {
        $notification = new AdminNotification(
            'Test Title',
            'Test Message',
            'danger'
        );
        $user = User::factory()->create();

        $data = $notification->toArray($user);

        expect($data)->toBeArray();
        expect($data['title'])->toBe('Test Title');
        expect($data['message'])->toBe('Test Message');
        expect($data['type'])->toBe('danger');
        expect($data['sent_at'])->not()->toBeNull();
    });

    it('can be sent to a user', function () {
        Notification::fake();

        $user = User::factory()->create();
        $notification = new AdminNotification(
            'Test Title',
            'Test Message',
            'info'
        );

        $user->notify($notification);

        Notification::assertSentTo($user, AdminNotification::class);
    });

    it('can be sent to multiple users', function () {
        Notification::fake();

        $users = User::factory()->count(3)->create();
        $notification = new AdminNotification(
            'Bulk Notification',
            'This is a bulk message',
            'info'
        );

        foreach ($users as $user) {
            $user->notify($notification);
        }

        Notification::assertSentTo($users, AdminNotification::class);
    });

    it('handles different notification types correctly', function () {
        $types = ['info', 'success', 'warning', 'danger'];

        foreach ($types as $type) {
            $notification = new AdminNotification(
                "Test {$type}",
                "Message for {$type}",
                $type
            );

            expect($notification->type)->toBe($type);

            $data = $notification->toArray(User::factory()->create());
            expect($data['type'])->toBe($type);
        }
    });

    it('includes timestamp in notification data', function () {
        $before = now();

        $notification = new AdminNotification('Test', 'Message');
        $user = User::factory()->create();

        $data = $notification->toDatabase($user);
        $after = now();

        $sentAt = \Carbon\Carbon::parse($data['sent_at']);

        expect($sentAt->gte($before))->toBeTrue();
        expect($sentAt->lte($after))->toBeTrue();
    });
});
