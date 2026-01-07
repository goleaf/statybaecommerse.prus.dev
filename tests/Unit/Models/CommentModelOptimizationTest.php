<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Comment Model Optimization', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->task = Task::factory()->create();
    });

    it('uses optimized query structure for entity comments', function () {
        // Create test comments
        Comment::factory()->count(5)->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
            'user_id'          => $this->user->id,
            'is_approved'      => true,
        ]);

        // Test the optimized scope
        $query = Comment::forEntity($this->task);
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        // Verify query structure uses proper WHERE conditions for index usage
        expect($sql)->toContain('commentable_type');
        expect($sql)->toContain('commentable_id');

        // Verify the entity type and ID are in the bindings (may have additional scope bindings)
        expect($bindings)->toContain(Task::class);
        expect($bindings)->toContain($this->task->id);

        // Verify results
        $comments = $query->get();
        expect($comments)->toHaveCount(5);
    });

    it('efficiently loads paginated comments with proper eager loading', function () {
        // Create parent comments
        $parentComments = Comment::factory()->count(3)->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
            'user_id'          => $this->user->id,
            'is_approved'      => true,
            'parent_id'        => null,
        ]);

        // Create child comments
        foreach ($parentComments as $parent) {
            Comment::factory()->count(2)->create([
                'commentable_type' => Task::class,
                'commentable_id'   => $this->task->id,
                'user_id'          => $this->user->id,
                'is_approved'      => true,
                'parent_id'        => $parent->id,
            ]);
        }

        // Test paginated scope
        $query = Comment::paginatedForEntity($this->task, 10);

        // Verify eager loading is configured
        $eagerLoads = $query->getEagerLoads();
        expect($eagerLoads)->toHaveKey('user');
        expect($eagerLoads)->toHaveKey('children');

        $comments = $query->get();
        expect($comments)->toHaveCount(3); // Only root comments

        // Verify children are loaded
        foreach ($comments as $comment) {
            expect($comment->relationLoaded('children'))->toBeTrue();
            expect($comment->relationLoaded('user'))->toBeTrue();
            expect($comment->children)->toHaveCount(2);
        }
    });

    it('uses approved entity scope efficiently', function () {
        // Create mixed approved/unapproved comments
        Comment::factory()->count(3)->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
            'user_id'          => $this->user->id,
            'is_approved'      => true,
        ]);

        Comment::factory()->count(2)->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
            'user_id'          => $this->user->id,
            'is_approved'      => false,
        ]);

        $query = Comment::approvedForEntity($this->task);
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        // Verify compound WHERE clause for composite index usage
        expect($sql)->toContain('commentable_type');
        expect($sql)->toContain('commentable_id');
        expect($sql)->toContain('is_approved');

        // Verify the required values are in bindings
        expect($bindings)->toContain(Task::class);
        expect($bindings)->toContain($this->task->id);
        expect($bindings)->toContain(true);

        $approvedComments = $query->get();
        expect($approvedComments)->toHaveCount(3);

        foreach ($approvedComments as $comment) {
            expect($comment->is_approved)->toBeTrue();
        }
    });

    it('maintains proper polymorphic relationship performance', function () {
        // Create comments for different entity types
        $anotherTask = Task::factory()->create();

        Comment::factory()->count(5)->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
            'user_id'          => $this->user->id,
        ]);

        Comment::factory()->count(3)->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $anotherTask->id,
            'user_id'          => $this->user->id,
        ]);

        // Test that queries are properly isolated by entity
        $task1Comments = Comment::forEntity($this->task)->get();
        $task2Comments = Comment::forEntity($anotherTask)->get();

        expect($task1Comments)->toHaveCount(5);
        expect($task2Comments)->toHaveCount(3);

        // Verify no cross-contamination
        foreach ($task1Comments as $comment) {
            expect($comment->commentable_id)->toBe($this->task->id);
        }

        foreach ($task2Comments as $comment) {
            expect($comment->commentable_id)->toBe($anotherTask->id);
        }
    });

    it('handles nested comment hierarchies efficiently', function () {
        // Create a deep comment hierarchy
        $rootComment = Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
            'user_id'          => $this->user->id,
            'parent_id'        => null,
        ]);

        $level1Comment = Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
            'user_id'          => $this->user->id,
            'parent_id'        => $rootComment->id,
        ]);

        $level2Comment = Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id'   => $this->task->id,
            'user_id'          => $this->user->id,
            'parent_id'        => $level1Comment->id,
        ]);

        // Test hierarchy methods
        expect($rootComment->isRoot())->toBeTrue();
        expect($level1Comment->isReply())->toBeTrue();
        expect($level2Comment->getDepth())->toBe(2);

        $hierarchyPath = $level2Comment->getHierarchyPath();
        expect($hierarchyPath)->toHaveCount(3);
        expect($hierarchyPath[0]->id)->toBe($rootComment->id);
        expect($hierarchyPath[2]->id)->toBe($level2Comment->id);
    });
});

describe('Comment Model Performance Properties', function () {
    it('maintains consistent query performance with varying data sizes', function () {
        $user = User::factory()->create();
        $tasks = Task::factory()->count(5)->create();

        $queryTimes = [];

        foreach ($tasks as $index => $task) {
            // Create varying amounts of comments (100, 200, 300, 400, 500)
            $commentCount = ($index + 1) * 100;

            Comment::factory()->count($commentCount)->create([
                'commentable_type' => Task::class,
                'commentable_id'   => $task->id,
                'user_id'          => $user->id,
                'is_approved'      => true,
            ]);

            // Measure query time
            $startTime = microtime(true);
            Comment::approvedForEntity($task)->limit(20)->get();
            $queryTimes[] = microtime(true) - $startTime;
        }

        // Performance should remain consistent (no query should be more than 3x slower)
        $minTime = min($queryTimes);
        $maxTime = max($queryTimes);

        expect($maxTime / $minTime)->toBeLessThan(3.0);
    });

    it('efficiently handles concurrent polymorphic queries', function () {
        $user = User::factory()->create();
        $tasks = Task::factory()->count(10)->create();

        // Create comments for all tasks
        foreach ($tasks as $task) {
            Comment::factory()->count(50)->create([
                'commentable_type' => Task::class,
                'commentable_id'   => $task->id,
                'user_id'          => $user->id,
                'is_approved'      => true,
            ]);
        }

        // Simulate concurrent queries
        $startTime = microtime(true);

        $results = [];
        foreach ($tasks as $task) {
            $results[] = Comment::forEntity($task)->approved()->count();
        }

        $totalTime = microtime(true) - $startTime;

        // All queries should return correct counts
        foreach ($results as $count) {
            expect($count)->toBe(50);
        }

        // Total time should be reasonable for 10 concurrent-style queries
        expect($totalTime)->toBeLessThan(1.0);
    });
});
