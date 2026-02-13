<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->project = Project::factory()->create([
        'user_id' => $this->user->id,
        'type'    => 'personal',
    ]);
});

describe('Project Relationships', function () {
    test('project belongs to owner', function () {
        expect($this->project->owner->id)->toBe($this->user->id);
    });

    test('project can add members', function () {
        $member = User::factory()->create();
        $this->project->addMember($member, 'lead', ['manage_projects']);

        expect($this->project->members)->toHaveCount(1);
        expect($this->project->leads)->toHaveCount(1);
        expect($this->project->members->first()->pivot->role)->toBe('lead');
    });

    test('project scopes work correctly', function () {
        $teamProject = Project::factory()->create(['type' => 'organizational']);

        expect(Project::personal()->count())->toBe(1);
        expect(Project::organizational()->count())->toBe(1);
        expect(Project::forUser($this->user)->count())->toBe(1);
        expect($this->project->isPersonal())->toBeTrue();
        expect($teamProject->isOrganizational())->toBeTrue();
    });
});

describe('Polymorphic Relationships', function () {
    test('models can have comments', function () {
        $comment = $this->project->addComment('Test comment', $this->user);

        expect($this->project->comments)->toHaveCount(1);
        expect($comment->commentable_type)->toBe(Project::class);
        expect($comment->commentable_id)->toBe($this->project->id);
    });

    test('models can have files', function () {
        $file = $this->project->attachFile([
            'name'          => 'test.pdf',
            'original_name' => 'test.pdf',
            'path'          => 'files/test.pdf',
            'mime_type'     => 'application/pdf',
            'size'          => 1024,
        ], $this->user);

        expect($this->project->files)->toHaveCount(1);
        expect($file->fileable_type)->toBe(Project::class);
        expect($file->uploaded_by)->toBe($this->user->id);
    });
});

describe('Nested Comments', function () {
    test('comments can have replies', function () {
        $rootComment = $this->project->addComment('Root comment', $this->user);
        $reply = Comment::factory()->create([
            'content'          => 'Reply comment',
            'user_id'          => $this->user->id,
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'parent_id'        => $rootComment->id,
        ]);

        expect($rootComment->children)->toHaveCount(1);
        expect($reply->parent->id)->toBe($rootComment->id);
        expect($reply->isReply())->toBeTrue();
        expect($rootComment->isRoot())->toBeTrue();
    });

    test('comment hierarchy methods work', function () {
        $root = $this->project->addComment('Root', $this->user);
        $child = Comment::factory()->create([
            'content'          => 'Child',
            'user_id'          => $this->user->id,
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'parent_id'        => $root->id,
        ]);
        $grandchild = Comment::factory()->create([
            'content'          => 'Grandchild',
            'user_id'          => $this->user->id,
            'commentable_type' => Project::class,
            'commentable_id'   => $this->project->id,
            'parent_id'        => $child->id,
        ]);

        expect($grandchild->getDepth())->toBe(2);
        expect($grandchild->getRootComment()->id)->toBe($root->id);
        expect($grandchild->getHierarchyPath())->toHaveCount(3);
    });
});

describe('Complex Queries', function () {
    test('can get user projects across ownership and membership', function () {
        $memberProject = Project::factory()->create(['type' => 'organizational']);
        $memberProject->addMember($this->user, 'member');

        $userProjects = Project::forUser($this->user)->get();

        expect($userProjects)->toHaveCount(2);
        expect($userProjects->pluck('id'))->toContain($memberProject->id, $this->project->id);
    });
});
