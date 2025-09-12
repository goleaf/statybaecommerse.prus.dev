<?php declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Notifications\TestNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

final class LiveNotificationService
{
    public function sendToAdmins(string $title, string $message, string $type = 'info'): void
    {
        $adminUsers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['administrator', 'manager']);
        })->get();

        foreach ($adminUsers as $user) {
            $this->sendToUser($user, $title, $message, $type);
        }
    }

    public function sendToUser(User $user, string $title, string $message, string $type = 'info'): void
    {
        $user->notify(new TestNotification($title, $message, $type));
        
        // Dispatch event for real-time updates
        Event::dispatch('notification.sent', [
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'timestamp' => now()->toISOString(),
        ]);
    }

    public function sendToUsers(Collection $users, string $title, string $message, string $type = 'info'): void
    {
        foreach ($users as $user) {
            $this->sendToUser($user, $title, $message, $type);
        }
    }

    public function sendSystemNotification(string $title, string $message, string $type = 'info'): void
    {
        $this->sendToAdmins($title, $message, $type);
    }

    public function sendOrderNotification(int $orderId, string $message, string $type = 'info'): void
    {
        $title = "Užsakymas #{$orderId}";
        $this->sendToAdmins($title, $message, $type);
    }

    public function sendStockAlert(string $productName, int $currentStock, int $threshold): void
    {
        $title = 'Mažos atsargos';
        $message = "Prekė \"{$productName}\" turi mažiau nei {$threshold} vienetų atsargų. Dabartinis kiekis: {$currentStock}";
        $this->sendToAdmins($title, $message, 'warning');
    }

    public function sendPaymentNotification(int $orderId, string $status): void
    {
        $title = 'Mokėjimo atnaujinimas';
        $message = "Užsakymo #{$orderId} mokėjimas: {$status}";
        $type = match($status) {
            'Sėkmingas' => 'success',
            'Nepavyko' => 'error',
            'Laukiama' => 'warning',
            default => 'info'
        };
        $this->sendToAdmins($title, $message, $type);
    }

    public function sendCustomerRegistrationNotification(string $customerEmail): void
    {
        $title = 'Naujas klientas';
        $message = "Registruotas naujas klientas: {$customerEmail}";
        $this->sendToAdmins($title, $message, 'success');
    }

    public function sendReviewNotification(string $productName, int $rating): void
    {
        $title = 'Naujas atsiliepimas';
        $message = "Prekė \"{$productName}\" gavo naują atsiliepimą: {$rating}/5 žvaigždučių";
        $this->sendToAdmins($title, $message, 'info');
    }
}
