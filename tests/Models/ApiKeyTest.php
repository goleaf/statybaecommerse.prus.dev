<?php

declare(strict_types=1);

use App\Enums\ApiKeyScope;
use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('generates credentials with hashed values', function (): void {
    // Act: generate a new set of credentials for an API key.
    $credentials = ApiKey::generateCredentials();

    // Assert: the plain text matches the expected prefix and hashing behavior.
    expect($credentials['plain_text'])
        ->toStartWith(ApiKey::KEY_PREFIX . '_')
        ->and($credentials['hashed'])
        ->toBe(hash('sha256', $credentials['plain_text']));
});

it('normalizes rate limit values consistently', function (): void {
    // Assert: various inputs resolve to the expected normalized integer or null.
    expect(ApiKey::normalizeRateLimit(null))->toBeNull()
        ->and(ApiKey::normalizeRateLimit(''))->toBeNull()
        ->and(ApiKey::normalizeRateLimit('0'))->toBeNull()
        ->and(ApiKey::normalizeRateLimit(0))->toBeNull()
        ->and(ApiKey::normalizeRateLimit('25'))->toBe(25)
        ->and(ApiKey::normalizeRateLimit(50))->toBe(50);
});

it('merges scopes and permissions without duplicates', function (): void {
    // Arrange: create an API key with overlapping scopes and permissions.
    $apiKey = ApiKey::factory()->create([
        'scopes'      => [ApiKeyScope::OrdersRead->value, '*'],
        'permissions' => ['orders.read', 'orders.write'],
    ]);

    // Act: resolve the scopes for downstream authorization checks.
    $resolved = $apiKey->resolvedScopes();

    // Assert: the wildcard is present and duplicates are removed.
    expect($resolved)
        ->toEqual([
            ApiKeyScope::OrdersRead->value,
            '*',
            'orders.write',
        ]);
});

it('aliases the legacy active attribute to is_active', function (): void {
    // Arrange: persist a record with the new attribute set to false.
    $apiKey = ApiKey::factory()->create(['is_active' => false]);

    // Assert: the legacy accessor reflects the stored state.
    expect($apiKey->active)->toBeFalse();

    // Act: toggle the accessor and persist the change.
    $apiKey->active = true;
    $apiKey->save();

    // Assert: the underlying attribute is updated when saving via the accessor.
    expect($apiKey->fresh()->is_active)->toBeTrue();
});

it('provides a belongs to relationship with user', function (): void {
    // Arrange: associate an API key with a specific user.
    $user = User::factory()->create();
    $apiKey = ApiKey::factory()->for($user)->create();

    // Assert: the relationship returns the expected model instance.
    expect($apiKey->user)->toBeInstanceOf(User::class)
        ->and($apiKey->user->is($user))->toBeTrue();
});

it('orders results using the ordered by name scope', function (): void {
    // Arrange: create keys with names that are intentionally out of order.
    $second = ApiKey::factory()->create(['name' => 'Zulu Integration Key']);
    $first = ApiKey::factory()->create(['name' => 'Alpha Integration Key']);

    // Act: fetch the ordered list of names using the custom scope.
    $names = ApiKey::query()->orderedByName()->pluck('name')->all();

    // Assert: ensure the alphabetical order matches expectations.
    expect($names)->toBe([
        $first->name,
        $second->name,
    ]);
});

it('regenerates credentials and returns the plain text values', function (): void {
    // Arrange: create a record with known credentials.
    $apiKey = ApiKey::factory()->create();
    $previousKey = $apiKey->key;
    $previousSecret = $apiKey->secret;

    // Act: rotate the credentials.
    $rotated = $apiKey->regenerateCredentials();

    // Assert: the stored values change and plain text responses are returned.
    expect($apiKey->fresh()->key)->not->toBe($previousKey);
    expect($apiKey->fresh()->secret)->not->toBe($previousSecret);
    expect($rotated['plain_text_key'])->toBeString();
    expect($rotated['plain_text_secret'])->toBeString();
    expect(strlen($rotated['plain_text_secret']))->toBe(64);
});

it('masks hashed keys safely for display', function (): void {
    // Arrange: craft a predictable hash string for easier assertions.
    $apiKey = ApiKey::factory()->create([
        'key' => hash('sha256', Str::random(10)),
    ]);

    // Act: mask the key while exposing a small portion.
    $masked = $apiKey->maskKey(4);

    // Assert: the masked key retains prefixes and suffixes with hidden middle characters.
    expect($masked)
        ->toStartWith(substr($apiKey->key, 0, 4))
        ->toEndWith(substr($apiKey->key, -4))
        ->toHaveLength(strlen($apiKey->key));
});
