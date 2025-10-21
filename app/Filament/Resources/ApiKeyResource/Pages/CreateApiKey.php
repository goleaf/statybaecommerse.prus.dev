<?php

declare(strict_types=1);

namespace App\Filament\Resources\ApiKeyResource\Pages;

use App\Filament\Resources\ApiKeyResource;
use App\Filament\Resources\ApiKeyResource\Concerns\HandlesApiKeyCredentials;
use App\Models\ApiKey;
use Filament\Resources\Pages\CreateRecord;

final class CreateApiKey extends CreateRecord
{
    use HandlesApiKeyCredentials;

    protected static string $resource = ApiKeyResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->generateFreshPlainTextKey();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $plainText = $data['plain_text_key'] ?? null;

        $credentials = filled($plainText)
            ? ApiKey::credentialsFromPlainText($plainText)
            : ApiKey::generateCredentials();

        $this->plainTextApiKey = $credentials['plain_text'];

        $data['key'] = $credentials['hashed'];
        $data['rate_limit'] = ApiKey::normalizeRateLimit($data['rate_limit'] ?? null);

        unset($data['plain_text_key']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->plainTextApiKey !== null) {
            $this->rememberPlainTextKey($this->record, $this->plainTextApiKey);
        }
    }

    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('api_keys.notifications.created');
    }
}
