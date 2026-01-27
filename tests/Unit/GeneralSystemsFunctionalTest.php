<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('General Systems Functional Test', function () {
    it('verifies comment system works with actual data and legacy models stay removed', function () {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $comment = Comment::factory()->create([
            'content'          => 'Test comment on project',
            'user_id'          => $user->id,
            'commentable_type' => Project::class,
            'commentable_id'   => $project->id,
            'is_approved'      => true,
        ]);

        expect($comment->user->id)->toBe($user->id);
        expect($comment->commentable->id)->toBe($project->id);
        expect($comment->isRoot())->toBeTrue();
        expect($project->comments()->count())->toBe(1);

        $reply = Comment::factory()->create([
            'content'          => 'Reply to comment',
            'user_id'          => $user->id,
            'commentable_type' => Project::class,
            'commentable_id'   => $project->id,
            'parent_id'        => $comment->id,
            'is_approved'      => true,
        ]);

        expect($reply->isReply())->toBeTrue();
        expect($reply->parent->id)->toBe($comment->id);
        expect($comment->children()->count())->toBe(1);
        expect($reply->getDepth())->toBe(1);

        $entityComments = Comment::forEntity($project)->get();
        expect($entityComments)->toHaveCount(2);

        $approvedComments = Comment::approvedForEntity($project)->get();
        expect($approvedComments)->toHaveCount(2);

        $rootComments = Comment::forEntity($project)->rootComments()->get();
        expect($rootComments)->toHaveCount(1);

        expect(class_exists('App\\Models\\Task'))->toBeFalse();
        expect(class_exists('App\\Models\\Tag'))->toBeFalse();
        expect(class_exists('App\\Models\\Taggable'))->toBeFalse();
    });
});
