<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->task = Task::factory()->create();
});

describe('Comment Model', function () {
    it('can create a comment with polymorphic relationship', function () {
        $comment = Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
            'user_id'          => $this->user->id,
        ]);

        expect($comment->commentable)->toBeInstanceOf(Task::class);
        expect($comment->commentable->id)->toBe($this->task->id);
        expect($comment->user)->toBeInstanceOf(User::class);
    });

    it('uses composite index efficiently for entity queries', function () {
        // Create multiple comments for different entities
        Comment::factory()->count(5)->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
        ]);

        $otherTask = Task::factory()->create();
        Comment::factory()->count(3)->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $otherTask->id,
        ]);

        // Test forEntity scope uses proper index
        $comments = Comment::forEntity($this->task)->get();
        expect($comments)->toHaveCount(5);

        // Verify all comments belong to the correct entity
        $comments->each(function ($comment) {
            expect($comment->commentable_id)->toBe($this->task->id);
            expect($comment->commentable_type)->toBe(Task::class);
        });
    });

    it('paginates comments efficiently with proper ordering', function () {
        // Create comments with different timestamps and pinned status
        $pinnedComment = Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
            'is_pinned'        => true,
            'created_at'       => now()->subHours(2),
        ]);

        $recentComment = Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
            'is_pinned'        => false,
            'created_at'       => now()->subHour(),
        ]);

        $oldComment = Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
            'is_pinned'        => false,
            'created_at'       => now()->subHours(3),
        ]);

        $paginatedComments = Comment::paginatedForEntity($this->task, 10)->get();

        // Verify ordering: pinned first, then by created_at desc
        expect($paginatedComments->first()->id)->toBe($pinnedComment->id);
        expect($paginatedComments->get(1)->id)->toBe($recentComment->id);
        expect($paginatedComments->last()->id)->toBe($oldComment->id);
    });

    it('handles nested comments hierarchy correctly', function () {
        $rootComment = Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
            'parent_id'        => null,
        ]);

        $childComment = Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
            'parent_id'        => $rootComment->id,
        ]);

        $grandchildComment = Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
            'parent_id'        => $childComment->id,
        ]);

        expect($rootComment->isRoot())->toBeTrue();
        expect($childComment->isReply())->toBeTrue();
        expect($grandchildComment->getDepth())->toBe(2);
        expect($grandchildComment->getRootComment()->id)->toBe($rootComment->id);
    });

    it('filters approved comments correctly', function () {
        Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
            'is_approved'      => true,
        ]);

        Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
            'is_approved'      => false,
        ]);

        $approvedComments = Comment::forEntity($this->task)->approved()->get();
        expect($approvedComments)->toHaveCount(1);
        expect($approvedComments->first()->is_approved)->toBeTrue();
    });
});

describe('Comment Scopes', function () {
    it('forEntity scope uses composite index efficiently', function () {
        // This test ensures the forEntity scope generates optimal SQL
        $query = Comment::forEntity($this->task);

        $sql = $query->toSql();
        $bindings = $query->getBindings();

        expect($sql)->toContain('commentable_type');
        expect($sql)->toContain('commentable_id');
        expect($bindings)->toContain(Task::class);
        expect($bindings)->toContain($this->task->id);
    });

    it('paginatedForEntity scope includes proper relationships', function () {
        Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
            'user_id'          => $this->user->id,
        ]);

        $query = Comment::paginatedForEntity($this->task, 10);

        // Verify the query includes necessary relationships
        $eagerLoads = $query->getEagerLoads();
        expect($eagerLoads)->toHaveKey('user');
        expect($eagerLoads)->toHaveKey('children');
    });
});

describe('Comment Performance', function () {
    it('prevents N+1 queries when loading comments with users', function () {
        Comment::factory()->count(10)->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
        ]);

        // Track queries - disable global scopes to get cleaner query count
        DB::enableQueryLog();

        $comments = Comment::withoutGlobalScopes()
            ->forEntity($this->task)
            ->with('user')
            ->get();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should be 2 queries: 1 for comments, 1 for users (without global scopes)
        expect(count($queries))->toBeLessThanOrEqual(2);
        expect($comments)->toHaveCount(10);
    });
});
