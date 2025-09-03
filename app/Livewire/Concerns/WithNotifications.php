<?php declare(strict_types=1);

namespace App\Livewire\Concerns;

trait WithNotifications
{
    public function notifySuccess(string $message, string $title = null): void
    {
        $this->dispatch('notify', [
            'type' => 'success',
            'title' => $title ?? __('Success'),
            'message' => $message,
        ]);
    }

    public function notifyError(string $message, string $title = null): void
    {
        $this->dispatch('notify', [
            'type' => 'error',
            'title' => $title ?? __('Error'),
            'message' => $message,
        ]);
    }

    public function notifyWarning(string $message, string $title = null): void
    {
        $this->dispatch('notify', [
            'type' => 'warning',
            'title' => $title ?? __('Warning'),
            'message' => $message,
        ]);
    }

    public function notifyInfo(string $message, string $title = null): void
    {
        $this->dispatch('notify', [
            'type' => 'info',
            'title' => $title ?? __('Information'),
            'message' => $message,
        ]);
    }
}