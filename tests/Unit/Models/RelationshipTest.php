<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create();
    $this->project = Project::factory()->create(['organization_id' => $this->organization->id]);
});

describe('Organization Relationships', function () {
    test('organization has users with roles', function () {
        $this->organization->addUser($this->user, 'admin', ['manage_projects']);

        expect($this->organization->users)->toHaveCount(1);
        expect($this->organization->users->first()->pivot->role)->toBe('admin');
        expect($this->organization->users->first()->pivot->permissions)->toContain('manage_projects');
    });

    test('organization has projects', function () {
        expect($this->organization->projects)->toHaveCount(1);
        expect($this->organization->projects->first()->id)->toBe($this->project->id);
    });

    test('organization can check user membership', function () {
        $this->organization->addUser($this->user, 'member');

        expect($this->organization->hasMember($this->user))->toBeTrue();
        expect($this->organization->userHasRole($this->user, 'member'))->toBeTrue();
        expect($this->organization->userHasRole($this->user, 'admin'))->toBeFalse();
    });
});

describe('Project Relationships', function () {
    test('project belongs to organization', function () {
        expect($this->project->organization->id)->toBe($this->organization->id);
    });

    test('project can add members', function () {
        $this->project->addMember($this->user, 'lead', ['manage_projects']);

        expect($this->project->members)->toHaveCount(1);
        expect($this->project->leads)->toHaveCount(1);
        expect($this->project->members->first()->pivot->role)->toBe('lead');
    });

    test('project scopes work correctly', function () {
        $personalProject = Project::factory()->create(['type' => 'personal', 'user_id' => $this->user->id]);

        expect(Project::personal()->count())->toBe(1);
        expect(Project::organizational()->count())->toBe(1);
        expect(Project::forUser($this->user)->count())->toBe(1);
        expect($personalProject->isPersonal())->toBeTrue();
        expect($this->project->isOrganizational())->toBeTrue();
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
    test('can get user projects across organizations', function () {
        $this->organization->addUser($this->user, 'member');
        $personalProject = Project::factory()->create(['type' => 'personal', 'user_id' => $this->user->id]);

        $userProjects = Project::forUser($this->user)->get();

        expect($userProjects)->toHaveCount(2);
        expect($userProjects->pluck('id'))->toContain($this->project->id, $personalProject->id);
    });
});
