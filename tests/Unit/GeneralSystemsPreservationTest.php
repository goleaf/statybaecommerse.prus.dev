<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Taggable;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;


uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->task = Task::factory()->create();
    $this->project = Project::factory()->create();
    $this->organization = Organization::factory()->create();
});

describe('General Systems Preservation Property Tests', function () {
    /**
     * **Feature: news-blog-cleanup-upgrade, Property 3: General systems preservation**
     * **Validates: Requirements 3.1, 3.2, 3.3, 3.4**
     *
     * For any entity that uses the general Tag or Comment system (Task, User, etc.),
     * after News-specific cleanup, all tag and comment operations should continue
     * to work correctly.
     */
    it('ensures general tag and comment systems remain functional', function () {
        // Property: General Tag system should remain fully functional for other entities
        assertGeneralTagSystemWorks();

        // Property: General Comment system should remain fully functional for other entities
        assertGeneralCommentSystemWorks();

        // Property: Tag and Comment models should preserve all functionality
        assertTagAndCommentModelsIntact();

        // Property: Polymorphic relationships should work correctly
        assertPolymorphicRelationshipsWork();
    });
});

function assertGeneralTagSystemWorks(): void
{
    // Test Tag model functionality
    $tag = Tag::factory()->create([
        'name' => 'test-tag',
        'type' => 'general',
    ]);

    expect($tag)->toBeInstanceOf(Tag::class);
    expect($tag->name)->toBe('test-tag');
    expect($tag->type)->toBe('general');

    // Test Tag relationships with various entities
    $user = test()->user;
    $task = test()->task;
    $project = test()->project;
    $organization = test()->organization;

    // Test tagging functionality for each entity type
    $entities = [
        'user' => $user,
        'task' => $task,
        'project' => $project,
        'organization' => $organization,
    ];

    foreach ($entities as $entityType => $entity) {
        // Create taggable relationship manually
        $taggable = new Taggable([
            'tag_id' => $tag->id,
            'taggable_type' => get_class($entity),
            'taggable_id' => $entity->id,
            'tagged_by' => $user->id,
            'tagged_at' => now(),
        ]);
        $taggable->save();

        // Verify the relationship works
        expect($tag->taggables()->where('taggable_type', get_class($entity))->exists())
            ->toBeTrue("Tag should be associated with {$entityType}");
    }

    // Test Tag scopes and methods
    expect($tag->getUsageCount())->toBe(4, 'Tag usage count should be correct');

    $taggedModels = $tag->getTaggedModels();
    expect($taggedModels)->toBeArray('Tagged models should return array');
    expect($taggedModels)->toHaveCount(4, 'Should have 4 different entity types tagged');
}

function assertGeneralCommentSystemWorks(): void
{
    $user = test()->user;
    $task = test()->task;
    $project = test()->project;
    $organization = test()->organization;

    // Test Comment functionality for each entity type
    $entities = [
        'task' => $task,
        'project' => $project,
        'organization' => $organization,
    ];

    foreach ($entities as $entityType => $entity) {
        // Create root comment
        $rootComment = Comment::factory()->create([
            'content' => "Root comment for {$entityType}",
            'user_id' => $user->id,
            'commentable_type' => get_class($entity),
            'commentable_id' => $entity->id,
            'is_approved' => true,
        ]);

        // Create reply comment
        $replyComment = Comment::factory()->create([
            'content' => "Reply to {$entityType} comment",
            'user_id' => $user->id,
            'commentable_type' => get_class($entity),
            'commentable_id' => $entity->id,
            'parent_id' => $rootComment->id,
            'is_approved' => true,
        ]);

        // Test comment relationships and methods
        expect($rootComment->isRoot())->toBeTrue('Root comment should be identified as root');
        expect($replyComment->isReply())->toBeTrue('Reply comment should be identified as reply');
        expect($rootComment->getDepth())->toBe(0, 'Root comment depth should be 0');
        expect($replyComment->getDepth())->toBe(1, 'Reply comment depth should be 1');

        // Test comment scopes
        $entityComments = Comment::forEntity($entity)->get();
        expect($entityComments)->toHaveCount(2, "Should have 2 comments for {$entityType}");

        $approvedComments = Comment::approvedForEntity($entity)->get();
        expect($approvedComments)->toHaveCount(2, "Should have 2 approved comments for {$entityType}");

        $rootComments = Comment::forEntity($entity)->rootComments()->get();
        expect($rootComments)->toHaveCount(1, "Should have 1 root comment for {$entityType}");
    }
}

function assertTagAndCommentModelsIntact(): void
{
    // Test Tag model has all expected methods
    $tagReflection = new ReflectionClass(Tag::class);
    $tagMethods = array_map(fn($method) => $method->getName(), $tagReflection->getMethods());

    $expectedTagMethods = [
        'taggables',
        'users',
        'organizations',
        'projects',
        'tasks',
        'comments',
        'files',
        'scopeByType',
        'scopePopular',
        'getUsageCount',
        'getTaggedModels',
    ];

    foreach ($expectedTagMethods as $method) {
        expect($tagMethods)->toContain($method, "Tag model should have {$method} method");
    }

    // Test Comment model has all expected methods
    $commentReflection = new ReflectionClass(Comment::class);
    $commentMethods = array_map(fn($method) => $method->getName(), $commentReflection->getMethods());

    $expectedCommentMethods = [
        'user',
        'commentable',
        'parent',
        'children',
        'descendants',
        'files',
        'tags',
        'scopeApproved',
        'scopePinned',
        'scopeRootComments',
        'scopeReplies',
        'scopeByUser',
        'scopeRecent',
        'scopeForEntity',
        'scopePaginatedForEntity',
        'scopeApprovedForEntity',
        'isRoot',
        'isReply',
        'getDepth',
        'getRootComment',
        'getHierarchyPath',
        'getTotalRepliesCount',
        'approve',
        'pin',
        'unpin',
    ];

    foreach ($expectedCommentMethods as $method) {
        expect($commentMethods)->toContain($method, "Comment model should have {$method} method");
    }
}

function assertPolymorphicRelationshipsWork(): void
{
    $user = test()->user;
    $task = test()->task;
    $tag = Tag::factory()->create();

    // Test polymorphic tagging
    $taggable = new Taggable([
        'tag_id' => $tag->id,
        'taggable_type' => Task::class,
        'taggable_id' => $task->id,
        'tagged_by' => $user->id,
        'tagged_at' => now(),
    ]);
    $taggable->save();

    // Test polymorphic relationships work correctly
    expect($taggable->tag)->toBeInstanceOf(Tag::class);
    expect($taggable->taggable)->toBeInstanceOf(Task::class);
    expect($taggable->tagger)->toBeInstanceOf(User::class);
    expect($taggable->tag->id)->toBe($tag->id);
    expect($taggable->taggable->id)->toBe($task->id);
    expect($taggable->tagger->id)->toBe($user->id);

    // Test polymorphic commenting
    $comment = Comment::factory()->create([
        'content' => 'Test polymorphic comment',
        'user_id' => $user->id,
        'commentable_type' => Task::class,
        'commentable_id' => $task->id,
    ]);

    expect($comment->user)->toBeInstanceOf(User::class);
    expect($comment->commentable)->toBeInstanceOf(Task::class);
    expect($comment->user->id)->toBe($user->id);
    expect($comment->commentable->id)->toBe($task->id);

    // Test that entities can access their tags and comments
    expect($task->tags()->exists())->toBeTrue('Task should have tags relationship');
    expect($task->comments()->exists())->toBeTrue('Task should have comments relationship');

    $taskTags = $task->tags()->get();
    $taskComments = $task->comments()->get();

    expect($taskTags)->toHaveCount(1, 'Task should have 1 tag');
    expect($taskComments)->toHaveCount(1, 'Task should have 1 comment');
}
