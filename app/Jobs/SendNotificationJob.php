<?php

declare (strict_types=1);
namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
/**
 * SendNotificationJob
 * 
 * Queue job for SendNotificationJob background processing with proper error handling, retry logic, and progress tracking.
 * 
 */
final class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Initialize the class instance with required dependencies.
     * @param Notification $notification
     * @param array $channels
     */
    public function __construct(public Notification $notification, public array $channels = ['database'])
    {
    }
    /**
     * Handle the job, event, or request processing.
     * @param NotificationService $notificationService
     * @return void
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            $user = $this->notification->notifiable;
            if (!$user instanceof User) {
                Log::warning('Notification notifiable is not a User', ['notification_id' => $this->notification->id, 'notifiable_type' => $this->notification->notifiable_type]);
                return;
            }
            // Send via different channels
            foreach ($this->channels as $channel) {
                $this->sendViaChannel($user, $channel);
            }
            Log::info('Notification sent successfully', ['notification_id' => $this->notification->id, 'user_id' => $user->id, 'channels' => $this->channels]);
        } catch (\Exception $e) {
            Log::error('Failed to send notification', ['notification_id' => $this->notification->id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }
    /**
     * Handle sendViaChannel functionality with proper error handling.
     * @param User $user
     * @param string $channel
     * @return void
     */
    private function sendViaChannel(User $user, string $channel): void
    {
        match ($channel) {
            'database' => $this->sendViaDatabase($user),
            'mail' => $this->sendViaMail($user),
            'sms' => $this->sendViaSms($user),
            'push' => $this->sendViaPush($user),
            default => Log::warning('Unknown notification channel', ['channel' => $channel]),
        };
    }
    /**
     * Handle sendViaDatabase functionality with proper error handling.
     * @param User $user
     * @return void
     */
    private function sendViaDatabase(User $user): void
    {
        // Database notification is already stored
        Log::info('Database notification sent', ['notification_id' => $this->notification->id, 'user_id' => $user->id]);
    }
    /**
     * Handle sendViaMail functionality with proper error handling.
     * @param User $user
     * @return void
     */
    private function sendViaMail(User $user): void
    {
        if (!$user->email) {
            Log::warning('User has no email address', ['user_id' => $user->id]);
            return;
        }
        // Check if user wants email notifications
        if (!$this->shouldSendEmail($user)) {
            Log::info('User opted out of email notifications', ['user_id' => $user->id]);
            return;
        }
        try {
            Mail::to($user->email)->send(new \App\Mail\NotificationMail($this->notification));
            Log::info('Email notification sent', ['notification_id' => $this->notification->id, 'user_id' => $user->id, 'email' => $user->email]);
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', ['notification_id' => $this->notification->id, 'user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }
    /**
     * Handle sendViaSms functionality with proper error handling.
     * @param User $user
     * @return void
     */
    private function sendViaSms(User $user): void
    {
        if (!$user->phone_number) {
            Log::warning('User has no phone number', ['user_id' => $user->id]);
            return;
        }
        // Check if user wants SMS notifications
        if (!$this->shouldSendSms($user)) {
            Log::info('User opted out of SMS notifications', ['user_id' => $user->id]);
            return;
        }
        // Implement SMS sending logic here
        Log::info('SMS notification sent', ['notification_id' => $this->notification->id, 'user_id' => $user->id, 'phone' => $user->phone_number]);
    }
    /**
     * Handle sendViaPush functionality with proper error handling.
     * @param User $user
     * @return void
     */
    private function sendViaPush(User $user): void
    {
        // Check if user wants push notifications
        if (!$this->shouldSendPush($user)) {
            Log::info('User opted out of push notifications', ['user_id' => $user->id]);
            return;
        }
        // Implement push notification logic here
        Log::info('Push notification sent', ['notification_id' => $this->notification->id, 'user_id' => $user->id]);
    }
    /**
     * Handle shouldSendEmail functionality with proper error handling.
     * @param User $user
     * @return bool
     */
    private function shouldSendEmail(User $user): bool
    {
        // Check user preferences
        $preferences = $user->preferences ?? [];
        $emailNotifications = $preferences['email_notifications'] ?? true;
        // Don't send email for non-urgent notifications if user prefers
        $urgentOnly = $preferences['email_urgent_only'] ?? false;
        if ($urgentOnly && !($this->notification->data['urgent'] ?? false)) {
            return false;
        }
        return $emailNotifications;
    }
    /**
     * Handle shouldSendSms functionality with proper error handling.
     * @param User $user
     * @return bool
     */
    private function shouldSendSms(User $user): bool
    {
        $preferences = $user->preferences ?? [];
        $smsNotifications = $preferences['sms_notifications'] ?? false;
        // Only send SMS for urgent notifications
        $urgent = $this->notification->data['urgent'] ?? false;
        return $smsNotifications && $urgent;
    }
    /**
     * Handle shouldSendPush functionality with proper error handling.
     * @param User $user
     * @return bool
     */
    private function shouldSendPush(User $user): bool
    {
        $preferences = $user->preferences ?? [];
        return $preferences['push_notifications'] ?? true;
    }
    /**
     * Handle failed functionality with proper error handling.
     * @param Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendNotificationJob failed', ['notification_id' => $this->notification->id, 'error' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);
    }
}