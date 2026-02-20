<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects /category/{slug} to localized /lt/categories/{slug} with 301', function (): void {
    $response = $this->get('/category/din444-varztai-su-kilpomis-cinkuoti');

    $response->assertStatus(301)
        ->assertRedirectContains('/lt/categories/din444-varztai-su-kilpomis-cinkuoti');
});

it('handles any slug in /category/{slug} redirect', function (): void {
    $response = $this->get('/category/some-other-slug');

    $response->assertStatus(301)
        ->assertRedirectContains('/lt/categories/some-other-slug');
});
