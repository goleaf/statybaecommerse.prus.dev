<?php

declare(strict_types=1);

namespace App\Filament\Resources\ApiKeyResource\Concerns;

use App\Models\ApiKey;
use Filament\Notifications\Notification;

trait HandlesApiKeyCredentials
{
    public function revealCredentials(): void
    {
        if (! $this->record instanceof ApiKey) {
            return;
        }

        $this->dispatchCredentialModal([
            'key' => $this->record->key,
            'secret' => $this->record->secret,
        ]);
    }

    public function regenerateCredentials(): void
    {
        if (! $this->record instanceof ApiKey) {
            return;
        }

        $credentials = $this->record->regenerateCredentials();

        $this->dispatchCredentialModal($credentials);

        Notification::make()
            ->title(__('api_keys.notifications.regenerated.title'))
            ->body(__('api_keys.notifications.regenerated.body', ['key' => $credentials['key']]))
            ->success()
            ->send();
    }

    /**
     * @param  array{key: string, secret: string|null}  $credentials
     */
    protected function dispatchCredentialModal(array $credentials): void
    {
        $this->dispatch('api-key:show', key: $credentials['key'], secret: $credentials['secret']);
    }
}
