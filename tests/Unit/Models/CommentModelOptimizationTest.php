<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Comment Model Optimization', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->project = Project::factory()->create();
    });

    it('uses optimized query structure for entity comments', function () {
        Comment::factory()->count(5)->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'user_id'          => $this->user->id,
            'is_approved'      => true,
        ]);

        $query = Comment::forEntity($this->project);
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        expect($sql)->toContain('commentable_type');
        expect($sql)->toContain('commentable_id');
        expect($bindings)->toContain(Project::class);
        expect($bindings)->toContain($this->project->id);

        $comments = $query->get();
        expect($comments)->toHaveCount(5);
    });

    it('efficiently loads paginated comments with proper eager loading', function () {
        $parentComments = Comment::factory()->count(3)->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'user_id'          => $this->user->id,
            'is_approved'      => true,
            'parent_id'        => null,
        ]);

        foreach ($parentComments as $parent) {
            Comment::factory()->count(2)->create([
                'commentable_type' => Project::class,
                'commentable_id'   => $this->project->id,
                'user_id'          => $this->user->id,
                'is_approved'      => true,
                'parent_id'        => $parent->id,
            ]);
        }

        $query = Comment::paginatedForEntity($this->project, 10);

        $eagerLoads = $query->getEagerLoads();
        expect($eagerLoads)->toHaveKey('user');
        expect($eagerLoads)->toHaveKey('children');

        $comments = $query->get();
        expect($comments)->toHaveCount(3);

        foreach ($comments as $comment) {
            expect($comment->relationLoaded('children'))->toBeTrue();
            expect($comment->relationLoaded('user'))->toBeTrue();
            expect($comment->children)->toHaveCount(2);
        }
    });

    it('uses approved entity scope efficiently', function () {
        Comment::factory()->count(3)->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'user_id'          => $this->user->id,
            'is_approved'      => true,
        ]);

        Comment::factory()->count(2)->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'user_id'          => $this->user->id,
            'is_approved'      => false,
        ]);

        $query = Comment::approvedForEntity($this->project);
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        expect($sql)->toContain('commentable_type');
        expect($sql)->toContain('commentable_id');
        expect($sql)->toContain('is_approved');
        expect($bindings)->toContain(Project::class);
        expect($bindings)->toContain($this->project->id);
        expect($bindings)->toContain(true);

        $approvedComments = $query->get();
        expect($approvedComments)->toHaveCount(3);

        foreach ($approvedComments as $comment) {
            expect($comment->is_approved)->toBeTrue();
        }
    });

    it('maintains proper polymorphic relationship performance', function () {
        $anotherProject = Project::factory()->create();

        Comment::factory()->count(5)->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'user_id'          => $this->user->id,
        ]);

        Comment::factory()->count(3)->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $anotherProject->id,
            'user_id'          => $this->user->id,
        ]);

        $project1Comments = Comment::forEntity($this->project)->get();
        $project2Comments = Comment::forEntity($anotherProject)->get();

        expect($project1Comments)->toHaveCount(5);
        expect($project2Comments)->toHaveCount(3);

        foreach ($project1Comments as $comment) {
            expect($comment->commentable_id)->toBe($this->project->id);
        }

        foreach ($project2Comments as $comment) {
            expect($comment->commentable_id)->toBe($anotherProject->id);
        }
    });

    it('handles nested comment hierarchies efficiently', function () {
        $rootComment = Comment::factory()->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'user_id'          => $this->user->id,
            'parent_id'        => null,
        ]);

        $level1Comment = Comment::factory()->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'user_id'          => $this->user->id,
            'parent_id'        => $rootComment->id,
        ]);

        $level2Comment = Comment::factory()->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'user_id'          => $this->user->id,
            'parent_id'        => $level1Comment->id,
        ]);

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
        $projects = Project::factory()->count(5)->create();

        $queryTimes = [];

        foreach ($projects as $index => $project) {
            $commentCount = ($index + 1) * 100;

            Comment::factory()->count($commentCount)->create([
                'commentable_type' => Project::class,
                'commentable_id'   => $project->id,
                'user_id'          => $user->id,
                'is_approved'      => true,
            ]);

            $startTime = microtime(true);
            Comment::approvedForEntity($project)->limit(20)->get();
            $queryTimes[] = microtime(true) - $startTime;
        }

        $minTime = min($queryTimes);
        $maxTime = max($queryTimes);

        // Guard against microsecond-level clock jitter when the fastest query is near-zero.
        $normalizedMinTime = max($minTime, 0.001);

        expect($maxTime / $normalizedMinTime)->toBeLessThan(6.0);
    });

    it('efficiently handles concurrent polymorphic queries', function () {
        $user = User::factory()->create();
        $projects = Project::factory()->count(10)->create();

        foreach ($projects as $project) {
            Comment::factory()->count(50)->create([
                'commentable_type' => Project::class,
                'commentable_id'   => $project->id,
                'user_id'          => $user->id,
                'is_approved'      => true,
            ]);
        }

        $startTime = microtime(true);

        $results = [];
        foreach ($projects as $project) {
            $results[] = Comment::forEntity($project)->approved()->count();
        }

        $totalTime = microtime(true) - $startTime;

        foreach ($results as $count) {
            expect($count)->toBe(50);
        }

        expect($totalTime)->toBeLessThan(1.0);
    });
});
