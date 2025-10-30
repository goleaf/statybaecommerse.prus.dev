<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Enums\ModerationState;
use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class PostResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Boot the Filament admin panel so Livewire pages adopt the expected guard/context.
        $this->resolveAdminPanel();

        // Normalise localisation output to keep table assertions deterministic across environments.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Create and authenticate the canonical admin user for policy and activity logging checks.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_posts(): void
    {
        // Seed a draft post so the listing has a concrete record to render.
        $post = Post::factory()->draft()->create([
            'title'              => 'Coverage Draft',
            'slug'               => 'coverage-draft',
            'user_id'            => $this->admin->id,
            'moderation_state'   => ModerationState::Draft->value,
            'submitted_for_review_at' => null,
        ]);

        // Hydrate the Livewire table and confirm the seeded draft appears for editors.
        Livewire::test(ListPosts::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$post]);
    }

    public function test_status_filter_limits_visible_records(): void
    {
        // Prepare contrasting posts so the status filter can isolate the published row.
        $draftPost = Post::factory()->draft()->create([
            'title'            => 'Draft Story',
            'slug'             => 'draft-story',
            'user_id'          => $this->admin->id,
            'moderation_state' => ModerationState::Draft->value,
        ]);
        $publishedPost = Post::factory()->published()->create([
            'title'            => 'Published Feature',
            'slug'             => 'published-feature',
            'user_id'          => $this->admin->id,
            'moderation_state' => ModerationState::Published->value,
        ]);

        // Apply the published filter and ensure only the published entry remains visible.
        Livewire::test(ListPosts::class)
            ->call('loadTable')
            ->filterTable('status', 'published')
            ->assertCanSeeTableRecords([$publishedPost])
            ->assertCanNotSeeTableRecords([$draftPost]);
    }

    public function test_moderation_state_filter_targets_review_queue(): void
    {
        // Create draft and review posts to exercise the moderation filter behaviour.
        $draftPost = Post::factory()->draft()->create([
            'title'            => 'Draft Perspective',
            'slug'             => 'draft-perspective',
            'user_id'          => $this->admin->id,
            'moderation_state' => ModerationState::Draft->value,
        ]);
        $reviewPost = Post::factory()->create([
            'title'                  => 'Review Spotlight',
            'slug'                   => 'review-spotlight',
            'user_id'                => $this->admin->id,
            'status'                 => 'draft',
            'moderation_state'       => ModerationState::Review->value,
            'submitted_for_review_at'=> now()->subDay(),
        ]);

        // Focus the table on review-ready submissions and confirm the draft stays hidden.
        Livewire::test(ListPosts::class)
            ->call('loadTable')
            ->filterTable('moderation_state', ModerationState::Review->value)
            ->assertCanSeeTableRecords([$reviewPost])
            ->assertCanNotSeeTableRecords([$draftPost]);
    }

    public function test_submit_for_review_action_transitions_post_to_review(): void
    {
        // Start from a draft so the action triggers the moderation state change.
        $post = Post::factory()->draft()->create([
            'title'            => 'Submission Draft',
            'slug'             => 'submission-draft',
            'user_id'          => $this->admin->id,
            'moderation_state' => ModerationState::Draft->value,
        ]);

        // Execute the submit action and ensure the record enters the review queue.
        Livewire::test(ListPosts::class)
            ->call('loadTable')
            ->callTableAction('submit_for_review', $post)
            ->assertHasNoTableActionErrors();

        $post->refresh();

        // Confirm moderation and timestamp metadata match the review workflow expectations.
        $this->assertSame(ModerationState::Review, $post->moderation_state);
        $this->assertNotNull($post->submitted_for_review_at);
        $this->assertSame('draft', $post->status);
    }

    public function test_approve_action_publishes_post_and_records_approval(): void
    {
        // Seed a review-stage post so the approve action can publish it.
        $post = Post::factory()->create([
            'title'                  => 'Review Candidate',
            'slug'                   => 'review-candidate',
            'user_id'                => $this->admin->id,
            'status'                 => 'draft',
            'moderation_state'       => ModerationState::Review->value,
            'submitted_for_review_at'=> now()->subDay(),
        ]);

        // Provide moderator notes through the table action form before executing the approval.
        Livewire::test(ListPosts::class)
            ->call('loadTable')
            ->mountTableAction('approve', $post)
            ->setTableActionData([
                'notes' => 'Looks great',
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $post->refresh();

        // The approval should publish the post and capture the moderator decision payload.
        $this->assertSame(ModerationState::Published, $post->moderation_state);
        $this->assertSame('published', $post->status);
        $this->assertSame($this->admin->id, $post->approved_by_id);
        $this->assertNotNull($post->approved_at);
        $this->assertTrue($post->approvals()->where('decision', 'approved')->exists());
    }

    public function test_request_changes_action_returns_post_to_draft(): void
    {
        // Begin with a published post so the change request rolls it back to draft status.
        $post = Post::factory()->published()->create([
            'title'            => 'Needs Revisions',
            'slug'             => 'needs-revisions',
            'user_id'          => $this->admin->id,
            'moderation_state' => ModerationState::Published->value,
        ]);

        // Trigger the request changes flow with moderator feedback included.
        Livewire::test(ListPosts::class)
            ->call('loadTable')
            ->mountTableAction('request_changes', $post)
            ->setTableActionData([
                'notes' => 'Please clarify pricing section',
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $post->refresh();

        // Ensure the record returns to draft, clears approval metadata, and logs the decision.
        $this->assertSame(ModerationState::Draft, $post->moderation_state);
        $this->assertSame('draft', $post->status);
        $this->assertNull($post->approved_at);
        $this->assertNull($post->approved_by_id);
        $this->assertTrue($post->approvals()->where('decision', 'returned')->exists());
    }

    public function test_publish_action_promotes_draft_post(): void
    {
        // Use a draft record so the publish action performs a manual promotion.
        $post = Post::factory()->draft()->create([
            'title'            => 'Manual Publish',
            'slug'             => 'manual-publish',
            'user_id'          => $this->admin->id,
            'moderation_state' => ModerationState::Draft->value,
            'published_at'     => null,
        ]);

        // Execute the publish action and validate the lifecycle transition.
        Livewire::test(ListPosts::class)
            ->call('loadTable')
            ->callTableAction('publish', $post)
            ->assertHasNoTableActionErrors();

        $post->refresh();

        $this->assertSame(ModerationState::Published, $post->moderation_state);
        $this->assertSame('published', $post->status);
        $this->assertNotNull($post->published_at);
    }

    public function test_unpublish_action_reverts_post_to_draft_state(): void
    {
        // Prepare a published record so the unpublish action can reset its workflow metadata.
        $post = Post::factory()->published()->create([
            'title'            => 'Unpublish Scenario',
            'slug'             => 'unpublish-scenario',
            'user_id'          => $this->admin->id,
            'moderation_state' => ModerationState::Published->value,
        ]);

        // Invoke the unpublish action and assert that publication details are cleared.
        Livewire::test(ListPosts::class)
            ->call('loadTable')
            ->callTableAction('unpublish', $post)
            ->assertHasNoTableActionErrors();

        $post->refresh();

        $this->assertSame(ModerationState::Draft, $post->moderation_state);
        $this->assertSame('draft', $post->status);
        $this->assertNull($post->published_at);
        $this->assertNull($post->approved_at);
        $this->assertNull($post->approved_by_id);
    }

    public function test_archive_action_marks_post_as_archived(): void
    {
        // Seed a non-archived post so the archive action has an eligible record.
        $post = Post::factory()->draft()->create([
            'title'            => 'Archive Target',
            'slug'             => 'archive-target',
            'user_id'          => $this->admin->id,
            'moderation_state' => ModerationState::Draft->value,
        ]);

        // Archive the record and verify the status reflects its retired state.
        Livewire::test(ListPosts::class)
            ->call('loadTable')
            ->callTableAction('archive', $post)
            ->assertHasNoTableActionErrors();

        $post->refresh();

        $this->assertSame('archived', $post->status);
        $this->assertSame(ModerationState::Draft, $post->moderation_state);
    }

    public function test_feature_action_sets_featured_flag(): void
    {
        // Create a standard post so the feature action can toggle the promotional flag.
        $post = Post::factory()->draft()->create([
            'title'            => 'Feature Me',
            'slug'             => 'feature-me',
            'user_id'          => $this->admin->id,
            'moderation_state' => ModerationState::Draft->value,
            'featured'         => false,
        ]);

        // Promote the post to featured and confirm the column updates accordingly.
        Livewire::test(ListPosts::class)
            ->call('loadTable')
            ->callTableAction('feature', $post)
            ->assertHasNoTableActionErrors();

        $this->assertTrue($post->refresh()->featured);
    }

    public function test_unfeature_action_clears_featured_flag(): void
    {
        // Start with a featured post to validate the unfeature action path.
        $post = Post::factory()->published()->create([
            'title'            => 'Already Featured',
            'slug'             => 'already-featured',
            'user_id'          => $this->admin->id,
            'moderation_state' => ModerationState::Published->value,
            'featured'         => true,
        ]);

        // Remove the featured flag and ensure the persisted record reflects the change.
        Livewire::test(ListPosts::class)
            ->call('loadTable')
            ->callTableAction('unfeature', $post)
            ->assertHasNoTableActionErrors();

        $this->assertFalse($post->refresh()->featured);
    }

    public function test_feature_filter_limits_results(): void
    {
        // Populate featured and standard posts to exercise the ternary filter.
        $featuredPost = Post::factory()->published()->create([
            'title'            => 'Featured Highlight',
            'slug'             => 'featured-highlight',
            'user_id'          => $this->admin->id,
            'moderation_state' => ModerationState::Published->value,
            'featured'         => true,
        ]);
        $standardPost = Post::factory()->draft()->create([
            'title'            => 'Standard Article',
            'slug'             => 'standard-article',
            'user_id'          => $this->admin->id,
            'moderation_state' => ModerationState::Draft->value,
            'featured'         => false,
        ]);

        // Focus the table on featured entries and ensure non-featured posts remain hidden.
        Livewire::test(ListPosts::class)
            ->call('loadTable')
            ->filterTable('featured', true)
            ->assertCanSeeTableRecords([$featuredPost])
            ->assertCanNotSeeTableRecords([$standardPost]);
    }
}
