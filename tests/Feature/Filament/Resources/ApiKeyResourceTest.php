<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Enums\ApiKeyScope;
use App\Filament\Resources\ApiKeyResource;
use App\Filament\Resources\ApiKeyResource\Pages\CreateApiKey;
use App\Filament\Resources\ApiKeyResource\Pages\EditApiKey;
use App\Filament\Resources\ApiKeyResource\Pages\ListApiKeys;
use App\Models\ApiKey;
use App\Models\User;
use App\Support\Nav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

final class ApiKeyResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_list_api_keys(): void
    {
        $apiKeys = ApiKey::factory()->count(2)->create();
        $unlimitedKey = ApiKey::factory()->unlimited()->create();
        $apiKeys->push($unlimitedKey);

        $this->actingAs($this->admin);

        Livewire::test(ListApiKeys::class)
            ->assertCanSeeTableRecords($apiKeys)
            ->assertSee(__('api_keys.rate_limit.unlimited'));
    }

    public function test_admin_can_create_api_key_with_normalized_rate_limit(): void
    {
        $this->actingAs($this->admin);

        $availableScopes = collect(ApiKeyScope::cases())
            ->map(static fn (ApiKeyScope $case): string => $case->value)
            ->all();
        $scopes = array_values(Arr::random($availableScopes, 2));

        Livewire::test(CreateApiKey::class)
            ->fillForm([
                'name'       => 'Public integration',
                'scopes'     => $scopes,
                'rate_limit' => '',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $apiKey = ApiKey::query()->firstOrFail();

        $this->assertSame('Public integration', $apiKey->name);
        $this->assertSame($scopes, $apiKey->scopes);
        $this->assertNull($apiKey->rate_limit);
        $this->assertTrue($apiKey->active);

        $plainText = session()->get($this->credentialSessionKey($apiKey));
        $this->assertIsString($plainText);
        $this->assertSame(ApiKey::hashKey($plainText), $apiKey->key);
    }

    public function test_admin_can_edit_api_key_and_update_plain_text_secret(): void
    {
        $apiKey = ApiKey::factory()->create([
            'name'       => 'Legacy key',
            'rate_limit' => 250,
        ]);

        $this->actingAs($this->admin);

        $newPlainText = sprintf('%s_%s', ApiKey::KEY_PREFIX, Str::upper(Str::random(ApiKey::KEY_LENGTH)));

        Livewire::test(EditApiKey::class, ['record' => $apiKey->getKey()])
            ->fillForm([
                'name'           => 'Legacy key v2',
                'rate_limit'     => 500,
                'plain_text_key' => $newPlainText,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $apiKey->refresh();

        $this->assertSame('Legacy key v2', $apiKey->name);
        $this->assertSame(500, $apiKey->rate_limit);
        $this->assertSame(ApiKey::hashKey($newPlainText), $apiKey->key);

        $plainText = session()->get($this->credentialSessionKey($apiKey));
        $this->assertSame($newPlainText, $plainText);
    }

    public function test_regenerate_table_action_updates_credentials_and_redirects(): void
    {
        $apiKey = ApiKey::factory()->create([
            'key'          => ApiKey::hashKey('sk_OLDKEY'),
            'last_used_at' => now(),
        ]);

        $this->actingAs($this->admin);

        $response = Livewire::test(ListApiKeys::class)
            ->callTableAction('regenerate', $apiKey);

        $apiKey->refresh();

        $this->assertNotSame(ApiKey::hashKey('sk_OLDKEY'), $apiKey->key);
        $this->assertNull($apiKey->last_used_at);

        $plainText = session()->get($this->credentialSessionKey($apiKey));
        $this->assertIsString($plainText);
        $this->assertSame(ApiKey::hashKey($plainText), $apiKey->key);

        $response->assertHasNoTableActionErrors();
    }

    public function test_reveal_action_visible_when_plain_text_is_available(): void
    {
        $apiKey = ApiKey::factory()->create();

        $this->actingAs($this->admin);

        session()->put($this->credentialSessionKey($apiKey), 'sk_FAKEKEY');

        Livewire::test(ListApiKeys::class)
            ->assertTableActionVisible('reveal', $apiKey);
    }

    public function test_navigation_and_labels_are_localized(): void
    {
        $this->assertSame(__('api_keys.navigation.label'), ApiKeyResource::getNavigationLabel());
        $this->assertSame(Nav::groupForResource(ApiKeyResource::class), ApiKeyResource::getNavigationGroup());
        $this->assertSame(__('api_keys.navigation.singular'), ApiKeyResource::getModelLabel());
        $this->assertSame(__('api_keys.navigation.plural'), ApiKeyResource::getPluralModelLabel());

        $labels = ApiKeyScope::options();
        foreach (ApiKeyScope::cases() as $case) {
            $this->assertArrayHasKey($case->value, $labels);
            $this->assertSame(__('api_keys.scopes.' . str_replace('.', '_', $case->value) . '.label'), $labels[$case->value]);
        }
    }

    private function credentialSessionKey(ApiKey $apiKey): string
    {
        return sprintf('filament.api_keys.%s.plain_text', $apiKey->getKey());
    }
}
