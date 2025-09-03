<?php declare(strict_types=1);

namespace App\Livewire\Concerns;

trait WithNotifications
{
    public function notifySuccess(string $message, ?string $title = null): void
    {
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $message,
            'title' => $title,
        ]);
    }

    public function notifyError(string $message, ?string $title = null): void
    {
        $this->dispatch('notify', [
            'type' => 'error',
            'message' => $message,
            'title' => $title,
        ]);
    }

    public function notifyWarning(string $message, ?string $title = null): void
    {
        $this->dispatch('notify', [
            'type' => 'warning',
            'message' => $message,
            'title' => $title,
        ]);
    }

    public function notifyInfo(string $message, ?string $title = null): void
    {
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => $message,
            'title' => $title,
        ]);
    }
}
