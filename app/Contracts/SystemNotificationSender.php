<?php

declare(strict_types=1);

namespace App\Contracts;

interface SystemNotificationSender
{
    public function sendSystemNotification(string $title, string $message, string $type = 'info'): void;
}
