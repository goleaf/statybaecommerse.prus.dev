<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class PostResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Boot the Filament admin panel so relation lookups and navigation behave as expected.
        $this->resolveAdminPanel();

        // Seed permissions so the super admin role inherits all moderation capabilities.
        $this->seed(RolesAndPermissionsSeeder::class);

        // Promote a reusable administrator with super admin access for the content workflows.
        $this->adminUser = User::factory()->create([
            'email'    => 'post-admin@example.test',
            'is_admin' => true,
        ]);
        $this->adminUser->assignRole('super_admin');
    }

    public function test_list_page_surfaces_published_and_draft_posts(): void
    {
        // Create contrasting posts to validate the resource bypasses the published scope for administrators.
        $draft = Post::factory()->draft()->create(['title' => 'Draft Article']);
        $published = Post::factory()->published()->create(['title' => 'Published Article']);

        Livewire::actingAs($this->adminUser)
            ->test(ListPosts::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$draft, $published]);
    }

    public function test_status_filter_restricts_results_to_requested_state(): void
    {
        // Prepare posts across multiple statuses so the filter output is deterministic.
        $draft = Post::factory()->draft()->create(['title' => 'Draft Article']);
        $published = Post::factory()->published()->create(['title' => 'Published Article']);

        Livewire::actingAs($this->adminUser)
            ->test(ListPosts::class)
            ->call('loadTable')
            ->filterTable('status', 'published')
            ->assertCanSeeTableRecords([$published])
            ->assertCanNotSeeTableRecords([$draft]);
    }
}
