<?php

declare(strict_types=1);

use App\Models\User;

it('updates brand translation', function (): void {
    $user = User::factory()->create(['email' => 'admin@example.com']);
    login($user);

    $payload = [
        'name' => 'Name',
        'slug' => 'slug',
        'description' => 'desc',
        'seo_title' => 'seo',
        'seo_description' => 'desc',
    ];

    $response = $this->put(route('admin.brands.translations.save', ['locale' => 'en', 'id' => 1, 'lang' => 'en']), $payload);
    $response->assertRedirect();
});

it('updates category translation', function (): void {
    login(User::factory()->create());
    $payload = [
        'name' => 'Name',
        'slug' => 'slug',
        'description' => 'desc',
        'seo_title' => 'seo',
        'seo_description' => 'desc',
    ];
    $response = $this->put(route('admin.categories.translations.save', ['locale' => 'en', 'id' => 1, 'lang' => 'en']), $payload);
    $response->assertRedirect();
});

it('updates collection translation', function (): void {
    login(User::factory()->create());
    $payload = [
        'name' => 'Name',
        'slug' => 'slug',
        'description' => 'desc',
    ];
    $response = $this->put(route('admin.collections.translations.save', ['locale' => 'en', 'id' => 1, 'lang' => 'en']), $payload);
    $response->assertRedirect();
});
