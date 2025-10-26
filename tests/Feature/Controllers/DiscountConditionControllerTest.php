<?php

declare(strict_types=1);

use App\Http\Middleware\TestingLegalResourceStub;
use App\Models\AdminUser;
use App\Models\Discount;
use App\Models\DiscountCondition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

it('paginates discount conditions with the approved filters', function (): void {
    // Create an administrator and authenticate against the admin guard for policy checks.
    $admin = AdminUser::factory()->create();
    actingAs($admin, 'admin');

    $discount = Discount::factory()->create(['name' => 'Filtered Discount']);
    $matchingCondition = DiscountCondition::factory()->active()->create([
        'discount_id' => $discount->id,
        'type'        => 'product',
        'operator'    => 'equals_to',
        'priority'    => 10,
    ]);
    DiscountCondition::factory()->active()->create([
        'type'     => 'category',
        'operator' => 'contains',
    ]);

    withoutMiddleware([TestingLegalResourceStub::class]);

    $response = get(route('discount-conditions.index', [
        'type'        => 'product',
        'discount_id' => $discount->id,
        'operator'    => 'equals_to',
        'per_page'    => 1,
    ]));

    $response->assertOk();
    $response->assertViewIs('discount-conditions.index');

    /** @var LengthAwarePaginator $paginator */
    $paginator = $response->viewData('conditions');

    // The filtered paginator should only expose the single matching record.
    expect($paginator->total())->toBe(1);
    expect($paginator->items()[0]->is($matchingCondition))->toBeTrue();
});

it('returns the structured evaluation payload from the test endpoint', function (): void {
    // Ensure an authenticated operator is present for the policy guard.
    $admin = AdminUser::factory()->create();
    actingAs($admin, 'admin');

    $condition = DiscountCondition::factory()->active()->create([
        'operator' => 'equals_to',
        'value'    => 'SAMPLE',
    ]);

    withoutMiddleware([TestingLegalResourceStub::class]);

    $response = postJson(route('discount-conditions.test', $condition), [
        'test_value' => 'SAMPLE',
    ]);

    $response->assertOk();
    $response->assertJson(
        fn ($json) => $json
            ->where('matches', true)
            ->where('is_valid', true)
            ->has('condition_description')
            ->where('message', fn (string $message): bool => $message !== '')
    );
});

it('exposes the operator allow list via the resource collection', function (): void {
    // Authenticate so the policy can run before returning JSON payloads.
    $admin = AdminUser::factory()->create();
    actingAs($admin, 'admin');

    withoutMiddleware([TestingLegalResourceStub::class]);

    $response = getJson(route('discount-conditions.api.operators-for-type', ['type' => 'product']));

    $response->assertOk();
    $response->assertJsonStructure(['operators' => [['key', 'label']]]);

    /** @var array<int, array<string, string>> $operatorsPayload */
    $operatorsPayload = $response->json('operators');
    $operators = collect($operatorsPayload);
    // The allowed operator keys should include the equality comparison.
    expect($operators->pluck('key'))->toContain('equals_to');
});
