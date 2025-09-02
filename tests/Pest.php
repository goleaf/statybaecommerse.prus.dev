<?php declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class)->in('Feature', 'Unit');

uses(RefreshDatabase::class)->in('Feature', 'Unit');

function login($user = null)
{
    $user ??= \App\Models\User::factory()->create();
    return test()->actingAs($user);
}

function get($uri, array $headers = [])
{
    return test()->get($uri, $headers);
}

function post($uri, array $data = [], array $headers = [])
{
    return test()->post($uri, $data, $headers);
}
