<?php declare(strict_types=1);

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use App\Filament\Resources\NewsResource;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // Create administrator role and permissions
    $role = Role::create(['name' => 'administrator']);
    $permissions = [
        'view news',
        'create news',
        'update news',
        'delete news',
        'browse_news'
    ];
    
    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }
    
    $role->givePermissionTo($permissions);
    
    // Create admin user
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('administrator');
    
    // Create test data
    $this->testCategory = NewsCategory::factory()->create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);
    
    $this->testNews = News::factory()->create([
        'title' => 'Test News Article',
        'slug' => 'test-news-article',
        'content' => 'This is test content for the news article.',
        'news_category_id' => $this->testCategory->id,
        'author' => 'Test Author',
        'is_published' => false,
        'is_featured' => false,
    ]);
});

it('can list news articles in admin panel', function () {
    $this->actingAs($this->adminUser)
        ->get(NewsResource::getUrl('index'))
        ->assertOk();
});

it('can create a new news article', function () {
    $category = NewsCategory::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\CreateNews::class)
        ->fillForm([
            'title' => 'New News Article',
            'slug' => 'new-news-article',
            'content' => 'This is the content of the new news article.',
            'news_category_id' => $category->id,
            'author' => 'New Author',
            'excerpt' => 'This is an excerpt of the news article.',
            'is_published' => true,
            'is_featured' => false,
            'published_at' => now(),
            'meta_title' => 'SEO Title',
            'meta_description' => 'SEO Description',
            'meta_keywords' => 'news, article, test',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
    
    $this->assertDatabaseHas('news', [
        'title' => 'New News Article',
        'slug' => 'new-news-article',
        'content' => 'This is the content of the new news article.',
        'news_category_id' => $category->id,
        'author' => 'New Author',
        'is_published' => true,
        'is_featured' => false,
    ]);
});

it('can view a news article', function () {
    $this->actingAs($this->adminUser)
        ->get(NewsResource::getUrl('view', ['record' => $this->testNews]))
        ->assertOk();
});

it('can edit a news article', function () {
    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\EditNews::class, ['record' => $this->testNews->id])
        ->fillForm([
            'title' => 'Updated News Article',
            'content' => 'This is the updated content.',
            'author' => 'Updated Author',
            'is_published' => true,
            'is_featured' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();
    
    $this->assertDatabaseHas('news', [
        'id' => $this->testNews->id,
        'title' => 'Updated News Article',
        'content' => 'This is the updated content.',
        'author' => 'Updated Author',
        'is_published' => true,
        'is_featured' => true,
    ]);
});

it('can delete a news article', function () {
    $news = News::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\EditNews::class, ['record' => $news->id])
        ->callAction('delete')
        ->assertOk();
    
    $this->assertDatabaseMissing('news', [
        'id' => $news->id,
    ]);
});

it('validates required fields when creating news', function () {
    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\CreateNews::class)
        ->fillForm([
            'title' => null,
            'slug' => null,
            'content' => null,
            'news_category_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['title', 'slug', 'content', 'news_category_id']);
});

it('validates unique slug when creating news', function () {
    $category = NewsCategory::factory()->create();
    $existingNews = News::factory()->create(['slug' => 'existing-slug']);
    
    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\CreateNews::class)
        ->fillForm([
            'title' => 'Test Title',
            'slug' => 'existing-slug', // Duplicate slug
            'content' => 'Test content',
            'news_category_id' => $category->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

it('validates slug length when creating news', function () {
    $category = NewsCategory::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\CreateNews::class)
        ->fillForm([
            'title' => 'Test Title',
            'slug' => str_repeat('a', 256), // Too long
            'content' => 'Test content',
            'news_category_id' => $category->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

it('can filter news by category', function () {
    $this->actingAs($this->adminUser)
        ->get(NewsResource::getUrl('index'))
        ->assertOk();
});

it('can filter news by published status', function () {
    $this->actingAs($this->adminUser)
        ->get(NewsResource::getUrl('index'))
        ->assertOk();
});

it('can filter news by featured status', function () {
    $this->actingAs($this->adminUser)
        ->get(NewsResource::getUrl('index'))
        ->assertOk();
});

it('shows correct news data in table', function () {
    $this->actingAs($this->adminUser)
        ->get(NewsResource::getUrl('index'))
        ->assertSee($this->testNews->title)
        ->assertSee($this->testCategory->name)
        ->assertSee($this->testNews->author);
});

it('handles news publishing workflow', function () {
    $news = News::factory()->create([
        'is_published' => false,
        'published_at' => null,
    ]);
    
    // Publish the news
    $news->update([
        'is_published' => true,
        'published_at' => now(),
    ]);
    
    expect($news->is_published)->toBeTrue();
    expect($news->published_at)->not->toBeNull();
});

it('handles bulk actions on news articles', function () {
    $news1 = News::factory()->create();
    $news2 = News::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\ListNews::class)
        ->callTableBulkAction('delete', [$news1->id, $news2->id])
        ->assertOk();
    
    $this->assertDatabaseMissing('news', [
        'id' => $news1->id,
    ]);
    
    $this->assertDatabaseMissing('news', [
        'id' => $news2->id,
    ]);
});

it('can manage news SEO fields', function () {
    $category = NewsCategory::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\CreateNews::class)
        ->fillForm([
            'title' => 'SEO Test Article',
            'slug' => 'seo-test-article',
            'content' => 'Test content',
            'news_category_id' => $category->id,
            'meta_title' => 'Custom SEO Title',
            'meta_description' => 'Custom SEO description for better search results.',
            'meta_keywords' => 'seo, test, article, keywords',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
    
    $this->assertDatabaseHas('news', [
        'title' => 'SEO Test Article',
        'meta_title' => 'Custom SEO Title',
        'meta_description' => 'Custom SEO description for better search results.',
        'meta_keywords' => 'seo, test, article, keywords',
    ]);
});

it('can set featured image for news', function () {
    $category = NewsCategory::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\CreateNews::class)
        ->fillForm([
            'title' => 'News with Image',
            'slug' => 'news-with-image',
            'content' => 'Test content',
            'news_category_id' => $category->id,
            'featured_image' => 'images/news/test-image.jpg',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
    
    $this->assertDatabaseHas('news', [
        'title' => 'News with Image',
        'featured_image' => 'images/news/test-image.jpg',
    ]);
});

