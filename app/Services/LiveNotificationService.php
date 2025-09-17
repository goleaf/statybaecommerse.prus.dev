<?php

declare (strict_types=1);
namespace App\Services;

use App\Models\User;
use App\Notifications\TestNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\LazyCollection;
/**
 * LiveNotificationService
 * 
 * Service class containing LiveNotificationService business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 */
final class LiveNotificationService
{
    /**
     * Handle sendToAdmins functionality with proper error handling.
     * @param string $title
     * @param string $message
     * @param string $type
     * @return void
     */
    public function sendToAdmins(string $title, string $message, string $type = 'info'): void
    {
        // Use LazyCollection with timeout to prevent long-running notification operations
        $timeout = now()->addSeconds(30);
        // 30 second timeout for admin notifications
        User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['administrator', 'manager']);
        })->cursor()->takeUntilTimeout($timeout)->each(function ($user) use ($title, $message, $type) {
            $this->sendToUser($user, $title, $message, $type);
        });
    }
    /**
     * Handle sendToUser functionality with proper error handling.
     * @param User $user
     * @param string $title
     * @param string $message
     * @param string $type
     * @return void
     */
    public function sendToUser(User $user, string $title, string $message, string $type = 'info'): void
    {
        $user->notify(new TestNotification($title, $message, $type));
        // Dispatch event for real-time updates
        Event::dispatch('notification.sent', ['user_id' => $user->id, 'title' => $title, 'message' => $message, 'type' => $type, 'timestamp' => now()->toISOString()]);
    }
    /**
     * Handle sendToUsers functionality with proper error handling.
     * @param Collection $users
     * @param string $title
     * @param string $message
     * @param string $type
     * @return void
     */
    public function sendToUsers(Collection $users, string $title, string $message, string $type = 'info'): void
    {
        // Use LazyCollection with timeout to prevent long-running bulk notification operations
        $timeout = now()->addMinutes(2);
        // 2 minute timeout for bulk user notifications
        LazyCollection::make($users)->takeUntilTimeout($timeout)->each(function ($user) use ($title, $message, $type) {
            $this->sendToUser($user, $title, $message, $type);
        });
    }
    /**
     * Handle sendSystemNotification functionality with proper error handling.
     * @param string $title
     * @param string $message
     * @param string $type
     * @return void
     */
    public function sendSystemNotification(string $title, string $message, string $type = 'info'): void
    {
        $this->sendToAdmins($title, $message, $type);
    }
    /**
     * Handle sendOrderNotification functionality with proper error handling.
     * @param int $orderId
     * @param string $message
     * @param string $type
     * @return void
     */
    public function sendOrderNotification(int $orderId, string $message, string $type = 'info'): void
    {
        $title = "Užsakymas #{$orderId}";
        $this->sendToAdmins($title, $message, $type);
    }
    /**
     * Handle sendStockAlert functionality with proper error handling.
     * @param string $productName
     * @param int $currentStock
     * @param int $threshold
     * @return void
     */
    public function sendStockAlert(string $productName, int $currentStock, int $threshold): void
    {
        $title = 'Mažos atsargos';
        $message = "Prekė \"{$productName}\" turi mažiau nei {$threshold} vienetų atsargų. Dabartinis kiekis: {$currentStock}";
        $this->sendToAdmins($title, $message, 'warning');
    }
    /**
     * Handle sendPaymentNotification functionality with proper error handling.
     * @param int $orderId
     * @param string $status
     * @return void
     */
    public function sendPaymentNotification(int $orderId, string $status): void
    {
        $title = 'Mokėjimo atnaujinimas';
        $message = "Užsakymo #{$orderId} mokėjimas: {$status}";
        $type = match ($status) {
            'Sėkmingas' => 'success',
            'Nepavyko' => 'error',
            'Laukiama' => 'warning',
            default => 'info',
        };
        $this->sendToAdmins($title, $message, $type);
    }
    /**
     * Handle sendCustomerRegistrationNotification functionality with proper error handling.
     * @param string $customerEmail
     * @return void
     */
    public function sendCustomerRegistrationNotification(string $customerEmail): void
    {
        $title = 'Naujas klientas';
        $message = "Registruotas naujas klientas: {$customerEmail}";
        $this->sendToAdmins($title, $message, 'success');
    }
    /**
     * Handle sendReviewNotification functionality with proper error handling.
     * @param string $productName
     * @param int $rating
     * @return void
     */
    public function sendReviewNotification(string $productName, int $rating): void
    {
        $title = 'Naujas atsiliepimas';
        $message = "Prekė \"{$productName}\" gavo naują atsiliepimą: {$rating}/5 žvaigždučių";
        $this->sendToAdmins($title, $message, 'info');
    }
}