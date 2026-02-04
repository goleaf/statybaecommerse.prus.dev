<?php

declare(strict_types=1);

use App\Models\ContactMessage;
use Database\Seeders\ContactMessageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('feature: seeds contact messages for admin review', function () {
    expect(ContactMessage::count())->toBe(0);

    $seeder = new ContactMessageSeeder;
    $seeder->run();

    expect(ContactMessage::count())->toBeGreaterThan(0);

    $message = ContactMessage::query()->first();

    expect($message)->not->toBeNull();
    expect($message?->name)->not->toBeEmpty();
    expect($message?->email)->not->toBeEmpty();
    expect($message?->subject)->not->toBeEmpty();
    expect($message?->message)->not->toBeEmpty();
    expect($message?->ip_address)->not->toBeEmpty();
    expect($message?->user_agent)->not->toBeEmpty();
});
