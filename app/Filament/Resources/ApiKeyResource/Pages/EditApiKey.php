<?php

declare(strict_types=1);

namespace App\Filament\Resources\ApiKeyResource\Pages;

use App\Filament\Resources\ApiKeyResource;
use App\Filament\Resources\ApiKeyResource\Concerns\HandlesApiKeyCredentials;
use App\Models\ApiKey;
use Filament\Resources\Pages\EditRecord;

final class EditApiKey extends EditRecord
{
    use HandlesApiKeyCredentials;

    protected static string $resource = ApiKeyResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        if ($plainText = $this->pullPlainTextKey($this->record)) {
            $state = $this->form->getState();
            $state['plain_text_key'] = $plainText;
            $this->form->fill($state);
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $plainText = $data['plain_text_key'] ?? null;

        if (filled($plainText)) {
            $credentials = ApiKey::credentialsFromPlainText($plainText);
            $data['key'] = $credentials['hashed'];
            $this->plainTextApiKey = $credentials['plain_text'];
        }

        $data['rate_limit'] = ApiKey::normalizeRateLimit($data['rate_limit'] ?? null);

        unset($data['plain_text_key']);

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->plainTextApiKey !== null) {
            $this->rememberPlainTextKey($this->record, $this->plainTextApiKey);
        }
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('api_keys.notifications.updated');
    }

    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
