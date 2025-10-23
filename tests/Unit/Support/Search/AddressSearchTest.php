<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\City;
use App\Models\User;
use App\Support\Search\AddressSearch;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Facades\Activity;

uses()->group('searchable-input');

beforeEach(function (): void {
    RefreshDatabaseState::$migrated = true;

    // Silence activity logging hooks so our lightweight schema definitions do not require
    // the vendor activity_log table that exists in the main application migration set.
    Activity::disableLogging();

    Schema::dropIfExists('addresses');
    Schema::dropIfExists('cities');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('cities', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('code')->nullable();
        $table->string('country_code')->nullable();
        $table->boolean('is_active')->default(true);
        $table->boolean('is_enabled')->default(true);
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('addresses', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('user_id')->nullable();
        $table->string('address_line_1')->nullable();
        $table->string('city')->nullable();
        $table->string('postal_code')->nullable();
        $table->string('country_code')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
    });
});

it('unit: suggests formatted addresses', function (): void {
    $user = User::unguarded(fn () => User::create([
        'name'  => 'Jonas',
        'email' => 'jonas@example.test',
    ]));

    Address::unguarded(fn () => Address::create([
        'user_id'        => $user->getKey(),
        'address_line_1' => 'Gedimino pr. 1',
        'city'           => 'Vilnius',
        'postal_code'    => '01103',
        'country_code'   => 'LT',
        'is_active'      => true,
    ]));

    $results = AddressSearch::labels('Gedimino');

    expect($results)
        ->toHaveCount(1)
        ->and($results[0])
        ->toContain('Vilnius');
});

it('unit: returns city search results with metadata', function (): void {
    $city = City::unguarded(fn () => City::create([
        'name'         => 'Kaunas',
        'code'         => 'KNS',
        'country_code' => 'LT',
        'is_active'    => true,
        'is_enabled'   => true,
    ]));

    $results = AddressSearch::cityResults('Kau');

    expect($results)
        ->toHaveCount(1)
        ->and($results[0]->value())
        ->toEqual((string) $city->getKey())
        ->and($results[0]->get('country_code'))
        ->toEqual('LT');
});
