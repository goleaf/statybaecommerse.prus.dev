<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Search\CustomerSearch;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Schema;

uses()->group('searchable-input');

beforeEach(function (): void {
    RefreshDatabaseState::$migrated = true;

    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->string('phone_number')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
});

it('unit: finds customers by email and phone', function (): void {
    $user = User::unguarded(fn () => User::create([
        'name'      => 'Aistė Statybaitė',
        'email'     => 'aiste@example.test',
        'phone'     => '+37060000000',
        'is_active' => true,
    ]));

    $results = CustomerSearch::byEmailPhoneName('aiste');

    expect($results)
        ->toHaveCount(1)
        ->and($results[0]->get('email'))
        ->toEqual('aiste@example.test')
        ->and($results[0]->get('customer_id'))
        ->toEqual($user->getKey());
});
