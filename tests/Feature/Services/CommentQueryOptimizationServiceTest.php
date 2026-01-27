<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use App\Services\CommentQueryOptimizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new CommentQueryOptimizationService;
    $this->user = User::factory()->create();
    $this->project = Project::factory()->create();
});

describe('CommentQueryOptimizationService', function () {
    it('gets paginated comments with proper caching', function () {
        Comment::factory()->count(25)->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'is_approved'      => true,
        ]);

        Cache::flush();
        $result1 = $this->service->getPaginatedComments($this->project, 1, 10);
        $result2 = $this->service->getPaginatedComments($this->project, 1, 10);

        expect($result1->total())->toBe(25);
        expect($result1->perPage())->toBe(10);
        expect($result1->items())->toHaveCount(10);
        expect($result2->total())->toBe($result1->total());
    });

    it('gets comment statistics efficiently', function () {
        Comment::factory()->count(5)->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'is_approved'      => true,
        ]);

        Comment::factory()->count(2)->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'is_approved'      => false,
        ]);

        Comment::factory()->count(3)->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'is_approved'      => true,
            'created_at'       => now()->subDays(2),
        ]);

        $stats = $this->service->getCommentStats($this->project);

        expect($stats['total_comments'])->toBe(10);
        expect($stats['approved_comments'])->toBe(8);
        expect($stats['pending_comments'])->toBe(2);
        expect($stats['recent_comments'])->toBe(5);
        expect($stats['top_commenters'])->toBeArray();
    });

    it('bulk approves comments efficiently', function () {
        $comments = Comment::factory()->count(5)->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'is_approved'      => false,
        ]);

        $commentIds = $comments->pluck('id')->toArray();
        $updated = $this->service->bulkApproveComments($commentIds);

        expect($updated)->toBe(5);

        $approvedCount = Comment::whereIn('id', $commentIds)
            ->where('is_approved', true)
            ->count();
        expect($approvedCount)->toBe(5);
    });

    it('gets comments for moderation with proper relationships', function () {
        Comment::factory()->count(15)->create([
            'is_approved' => false,
            'user_id'     => $this->user->id,
        ]);

        $result = $this->service->getCommentsForModeration(10);

        expect($result->total())->toBe(15);
        expect($result->perPage())->toBe(10);
        expect($result->items())->toHaveCount(10);

        $firstComment = $result->items()[0];
        expect($firstComment->relationLoaded('user'))->toBeTrue();
        expect($firstComment->relationLoaded('commentable'))->toBeTrue();
    });

    it('searches comments with proper filtering', function () {
        Comment::factory()->create([
            'content'          => 'This is a test comment about Laravel',
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'is_approved'      => true,
        ]);

        Comment::factory()->create([
            'content'     => 'Another comment about PHP',
            'is_approved' => true,
        ]);

        Comment::factory()->create([
            'content'     => 'Laravel framework is great',
            'is_approved' => false,
        ]);

        $results = $this->service->searchComments('Laravel');
        expect($results->total())->toBe(1);

        $entityResults = $this->service->searchComments('Laravel', $this->project);
        expect($entityResults->total())->toBe(1);
    });

    it('gets comment thread with all replies', function () {
        $rootComment = Comment::factory()->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'parent_id'        => null,
            'user_id'          => $this->user->id,
        ]);

        Comment::factory()->count(3)->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'parent_id'        => $rootComment->id,
            'is_approved'      => true,
        ]);

        $thread = $this->service->getCommentThread($rootComment);

        expect($thread->id)->toBe($rootComment->id);
        expect($thread->relationLoaded('user'))->toBeTrue();
        expect($thread->relationLoaded('descendants'))->toBeTrue();
        expect($thread->descendants)->toHaveCount(3);
    });
});

describe('Performance Tests', function () {
    it('handles large datasets efficiently', function () {
        Comment::factory()->count(1000)->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'is_approved'      => true,
        ]);

        $startTime = microtime(true);
        $result = $this->service->getPaginatedComments($this->project, 1, 20);
        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;

        expect($result->total())->toBe(1000);
        expect($result->items())->toHaveCount(20);
        expect($executionTime)->toBeLessThan(1.0);
    });

    it('caches results to improve subsequent requests', function () {
        Comment::factory()->count(100)->create([
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'is_approved'      => true,
        ]);

        Cache::flush();

        $start1 = microtime(true);
        $result1 = $this->service->getPaginatedComments($this->project, 1, 20);
        $time1 = microtime(true) - $start1;

        $start2 = microtime(true);
        $result2 = $this->service->getPaginatedComments($this->project, 1, 20);
        $time2 = microtime(true) - $start2;

        expect($result1->total())->toBe($result2->total());
        expect($time2)->toBeLessThan($time1);
    });
});
