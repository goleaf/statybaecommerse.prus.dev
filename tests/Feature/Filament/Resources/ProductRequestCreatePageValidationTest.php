<?php

declare(strict_types=1);

use App\Filament\Resources\ProductRequestResource\Pages\CreateProductRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

final class TestableCreateProductRequest extends CreateProductRequest
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function mutateForTest(array $data): array
    {
        return $this->mutateFormDataBeforeCreate($data);
    }
}

it('blocks create when required identity fields are missing', function (): void {
    $page = new TestableCreateProductRequest();

    try {
        $page->mutateForTest([
            'product_id'         => 1,
            'requested_quantity' => 1,
            'status'             => 'pending',
        ]);

        $this->fail('Expected validation exception was not thrown.');
    } catch (ValidationException $exception) {
        $errors = $exception->errors();

        expect($errors)->toHaveKeys(['user_id', 'name', 'email']);
    }
});

it('fills identity fields from selected user before create', function (): void {
    $user = User::factory()->create([
        'name'  => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+37060000000',
    ]);

    $page = new TestableCreateProductRequest();

    $payload = $page->mutateForTest([
        'product_id'         => 1,
        'requested_quantity' => 1,
        'user_id'            => $user->getKey(),
        'name'               => '',
        'email'              => '',
        'phone'              => '',
        'status'             => 'pending',
    ]);

    expect($payload['name'])->toBe('John Doe');
    expect($payload['email'])->toBe('john@example.com');
    expect($payload['phone'])->toBe('+37060000000');
});

it('blocks create when selected user does not exist', function (): void {
    $page = new TestableCreateProductRequest();

    try {
        $page->mutateForTest([
            'product_id'         => 1,
            'requested_quantity' => 1,
            'user_id'            => 999999,
            'name'               => 'Fallback',
            'email'              => 'fallback@example.com',
            'status'             => 'pending',
        ]);

        $this->fail('Expected validation exception was not thrown.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('user_id');
    }
});
