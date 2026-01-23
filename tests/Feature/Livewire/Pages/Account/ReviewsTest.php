<?php

declare(strict_types=1);

use App\Livewire\Pages\Account\Reviews;
use App\Models\Review;
use App\Models\User;
use Livewire\Livewire;

test('reviews component can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Reviews::class)
        ->assertStatus(200)
        ->assertSee(__('My reviews'));
});

test('reviews component shows user reviews', function () {
    $user = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $user->id, 'title' => 'Great product']);

    $this->actingAs($user);

    Livewire::test(Reviews::class)
        ->assertStatus(200)
        ->assertSee('Great product');
});

test('reviews component shows empty state when no reviews', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Reviews::class)
        ->assertStatus(200)
        ->assertSee(__('You have not posted any reviews yet.'));
});
