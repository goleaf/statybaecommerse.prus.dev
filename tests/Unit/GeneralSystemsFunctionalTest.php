<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Tag;
use App\Models\Taggable;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('General Systems Functional Test', function () {
    /**
     * Test that general Tag and Comment systems work functionally
     * This verifies Requirements 3.1, 3.2, 3.3, 3.4
     */
    it('verifies tag and comment systems work with actual data', function () {
        // Create test data
        $user = User::factory()->create();
        $task = Task::factory()->create();
        $tag = Tag::factory()->create([
            'name' => 'test-tag',
            'type' => 'general',
        ]);

        // Test tagging functionality
        $taggable = new Taggable([
            'tag_id'        => $tag->id,
            'taggable_type' => Task::class,
            'taggable_id'   => $task->id,
            'tagged_by'     => $user->id,
            'tagged_at'     => now(),
        ]);
        $taggable->save();

        // Verify tagging relationships work
        expect($tag->taggables()->count())->toBe(1, 'Tag should have 1 taggable relationship');
        expect($tag->getUsageCount())->toBe(1, 'Tag usage count should be 1');
        expect($task->tags()->count())->toBe(1, 'Task should have 1 tag');

        // Test commenting functionality
        $comment = Comment::factory()->create([
            'content'          => 'Test comment on task',
            'user_id'          => $user->id,
            'commentable_type' => Task::class,
            'commentable_id'   => $task->id,
            'is_approved'      => true,
        ]);

        // Verify commenting relationships work
        expect($comment->user->id)->toBe($user->id, 'Comment should belong to user');
        expect($comment->commentable->id)->toBe($task->id, 'Comment should belong to task');
        expect($comment->isRoot())->toBeTrue('Comment should be a root comment');
        expect($task->comments()->count())->toBe(1, 'Task should have 1 comment');

        // Test nested comments
        $reply = Comment::factory()->create([
            'content'          => 'Reply to comment',
            'user_id'          => $user->id,
            'commentable_type' => Task::class,
            'commentable_id'   => $task->id,
            'parent_id'        => $comment->id,
            'is_approved'      => true,
        ]);

        // Verify nested comment functionality
        expect($reply->isReply())->toBeTrue('Reply should be identified as reply');
        expect($reply->parent->id)->toBe($comment->id, 'Reply should have correct parent');
        expect($comment->children()->count())->toBe(1, 'Root comment should have 1 child');
        expect($reply->getDepth())->toBe(1, 'Reply depth should be 1');

        // Test comment scopes
        $entityComments = Comment::forEntity($task)->get();
        expect($entityComments)->toHaveCount(2, 'Should have 2 comments for task');

        $approvedComments = Comment::approvedForEntity($task)->get();
        expect($approvedComments)->toHaveCount(2, 'Should have 2 approved comments for task');

        $rootComments = Comment::forEntity($task)->rootComments()->get();
        expect($rootComments)->toHaveCount(1, 'Should have 1 root comment for task');
    });
});
