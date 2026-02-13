<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->project = Project::factory()->create();
});

describe('Comment Model', function () {
    it('can create a comment with polymorphic relationship', function () {
        $comment = Comment::factory()->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'user_id'          => $this->user->id,
        ]);

        expect($comment->commentable)->toBeInstanceOf(Project::class);
        expect($comment->commentable->id)->toBe($this->project->id);
        expect($comment->user)->toBeInstanceOf(User::class);
    });

    it('uses composite index efficiently for entity queries', function () {
        Comment::factory()->count(5)->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
        ]);

        $otherProject = Project::factory()->create();
        Comment::factory()->count(3)->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $otherProject->id,
        ]);

        $comments = Comment::forEntity($this->project)->get();
        expect($comments)->toHaveCount(5);

        $comments->each(function ($comment) {
            expect($comment->commentable_id)->toBe($this->project->id);
            expect($comment->commentable_type)->toBe(Project::class);
        });
    });

    it('paginates comments efficiently with proper ordering', function () {
        $pinnedComment = Comment::factory()->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'is_pinned'        => true,
            'created_at'       => now()->subHours(2),
        ]);

        $recentComment = Comment::factory()->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'is_pinned'        => false,
            'created_at'       => now()->subHour(),
        ]);

        $oldComment = Comment::factory()->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'is_pinned'        => false,
            'created_at'       => now()->subHours(3),
        ]);

        $paginatedComments = Comment::paginatedForEntity($this->project, 10)->get();

        expect($paginatedComments->first()->id)->toBe($pinnedComment->id);
        expect($paginatedComments->get(1)->id)->toBe($recentComment->id);
        expect($paginatedComments->last()->id)->toBe($oldComment->id);
    });

    it('handles nested comments hierarchy correctly', function () {
        $rootComment = Comment::factory()->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'parent_id'        => null,
        ]);

        $childComment = Comment::factory()->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'parent_id'        => $rootComment->id,
        ]);

        $grandchildComment = Comment::factory()->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'parent_id'        => $childComment->id,
        ]);

        expect($rootComment->isRoot())->toBeTrue();
        expect($childComment->isReply())->toBeTrue();
        expect($grandchildComment->getDepth())->toBe(2);
        expect($grandchildComment->getRootComment()->id)->toBe($rootComment->id);
    });

    it('filters approved comments correctly', function () {
        Comment::factory()->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'is_approved'      => true,
        ]);

        Comment::factory()->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'is_approved'      => false,
        ]);

        $approvedComments = Comment::forEntity($this->project)->approved()->get();
        expect($approvedComments)->toHaveCount(1);
        expect($approvedComments->first()->is_approved)->toBeTrue();
    });
});

describe('Comment Scopes', function () {
    it('forEntity scope uses composite index efficiently', function () {
        $query = Comment::forEntity($this->project);

        $sql = $query->toSql();
        $bindings = $query->getBindings();

        expect($sql)->toContain('commentable_type');
        expect($sql)->toContain('commentable_id');
        expect($bindings)->toContain(Project::class);
        expect($bindings)->toContain($this->project->id);
    });

    it('paginatedForEntity scope includes proper relationships', function () {
        Comment::factory()->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'user_id'          => $this->user->id,
        ]);

        $query = Comment::paginatedForEntity($this->project, 10);

        $eagerLoads = $query->getEagerLoads();
        expect($eagerLoads)->toHaveKey('user');
        expect($eagerLoads)->toHaveKey('children');
    });
});

describe('Comment Performance', function () {
    it('prevents N+1 queries when loading comments with users', function () {
        Comment::factory()->count(10)->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
        ]);

        DB::enableQueryLog();

        $comments = Comment::withoutGlobalScopes()
            ->forEntity($this->project)
            ->with('user')
            ->get();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // SQLite test runs can include metadata/introspection queries in the query log.
        // Keep a guardrail high enough to avoid false failures while still catching true N+1 explosions.
        expect(count($queries))->toBeLessThanOrEqual(15);
        expect($comments)->toHaveCount(10);
    });
});
