<?php

declare(strict_types=1);

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

describe('ContactMessage model', function (): void {
    it('stores fillable attributes correctly', function (): void {
        // Arrange: create a model instance to inspect the fillable configuration.
        $contactMessage = new ContactMessage();

        // Assert: the fillable array contains every mass-assignable attribute.
        expect($contactMessage->getFillable())->toMatchArray([
            'name',
            'email',
            'subject',
            'phone',
            'order_number',
            'message',
            'ip_address',
            'user_agent',
        ]);
    });

    it('casts timestamp attributes to carbon instances', function (): void {
        // Arrange: persist a record so the timestamps are populated.
        $contactMessage = ContactMessage::query()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Question',
            'message' => 'Could you help me?',
        ]);

        // Assert: the timestamps are automatically cast to Carbon instances.
        expect($contactMessage->created_at)->toBeInstanceOf(Carbon::class);
        expect($contactMessage->updated_at)->toBeInstanceOf(Carbon::class);
    });

    it('can order records alphabetically by name with the scopeOrderedByName scope', function (): void {
        // Arrange: create records in a non-alphabetical order to verify the scope sorting.
        ContactMessage::query()->create([
            'name' => 'Charlie Contact',
            'email' => 'charlie@example.com',
            'subject' => 'Support',
            'message' => 'Need some assistance.',
        ]);

        ContactMessage::query()->create([
            'name' => 'Alice Contact',
            'email' => 'alice@example.com',
            'subject' => 'Feedback',
            'message' => 'Just sharing feedback.',
        ]);

        ContactMessage::query()->create([
            'name' => 'Bob Contact',
            'email' => 'bob@example.com',
            'subject' => 'Request',
            'message' => 'Requesting more info.',
        ]);

        // Act: apply the query scope to retrieve the ordered names.
        $orderedNames = ContactMessage::query()->orderedByName()->pluck('name')->all();

        // Assert: the scope returns the names sorted alphabetically.
        expect($orderedNames)->toBe([
            'Alice Contact',
            'Bob Contact',
            'Charlie Contact',
        ]);
    });
});
