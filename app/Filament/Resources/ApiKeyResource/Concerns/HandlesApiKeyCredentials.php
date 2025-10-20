<?php

declare(strict_types=1);

namespace App\Filament\Resources\ApiKeyResource\Concerns;

use App\Models\ApiKey;

trait HandlesApiKeyCredentials
{
    protected ?string $plainTextApiKey = null;

    public function generateFreshPlainTextKey(): void
    {
        $credentials = ApiKey::generateCredentials();

        $this->plainTextApiKey = $credentials['plain_text'];

        $state = $this->form->getState();
        $state['plain_text_key'] = $credentials['plain_text'];
        $this->form->fill($state);

        $this->rememberPlainTextKey($this->record ?? null, $credentials['plain_text']);
    }

    public function rememberPlainTextKey(?ApiKey $record, string $plainText): void
    {
        $this->plainTextApiKey = $plainText;

        session()->flash(static::getCredentialSessionKey($record), $plainText);
    }

    public function pullPlainTextKey(?ApiKey $record): ?string
    {
        return session()->get(static::getCredentialSessionKey($record));
    }

    protected static function getCredentialSessionKey(ApiKey|int|string|null $record): string
    {
        $identifier = $record instanceof ApiKey ? $record->getKey() : $record;

        return sprintf('filament.api_keys.%s.plain_text', $identifier ?? 'draft');
    }
}
