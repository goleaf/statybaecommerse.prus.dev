<?php

declare(strict_types=1);

use App\Http\Requests\StoreReferralRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

it('validates referred_email exists and message length', function (): void {
    $user = User::factory()->create(['email' => 'known@example.com']);

    $request = new StoreReferralRequest;
    $rules = $request->rules();

    $valid = Validator::make([
        'referred_email' => 'known@example.com',
        'message'        => str_repeat('a', 500),
    ], $rules)->passes();

    expect($valid)->toBeTrue();

    $invalid = Validator::make([
        'referred_email' => 'missing@example.com',
        'message'        => str_repeat('b', 501),
    ], $rules)->passes();

    expect($invalid)->toBeFalse();
});
