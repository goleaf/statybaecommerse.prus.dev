<?php declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createAdminUser(): \App\Models\User
{
    return \App\Models\User::query()->create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
        'is_admin' => true,
    ]);
}

it('mounts News resource index page', function (): void {
    $admin = createAdminUser();
    $this->actingAs($admin);

    $response = $this->get('/admin/news');
    $response->assertStatus(200);
});

it('can create and edit a news item via admin endpoints', function (): void {
    $admin = createAdminUser();
    $this->actingAs($admin);

    // Create
    $create = $this->post('/admin/news', [
        'is_visible' => true,
        'published_at' => now()->toDateTimeString(),
        'author_name' => 'Admin',
        'translations' => [
            [
                'locale' => 'lt',
                'title' => 'Pirmoji naujiena',
                'slug' => 'pirmoji-naujiena',
                'summary' => 'Trumpas aprašymas',
            ],
            [
                'locale' => 'en',
                'title' => 'First news',
                'slug' => 'first-news',
                'summary' => 'Short summary',
            ],
        ],
    ]);

    $create->assertRedirect();

    /** @var \App\Models\News $news */
    $news = \App\Models\News::query()->latest('id')->first();
    expect($news)->not->toBeNull();

    // Update
    $update = $this->put("/admin/news/{$news->getKey()}", [
        'author_name' => 'Content Editor',
        'is_visible' => true,
        'translations' => [
            [
                'locale' => 'lt',
                'title' => 'Atnaujinta naujiena',
                'slug' => 'atnaujinta-naujiena',
            ],
        ],
    ]);

    $update->assertRedirect();
});
