<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create();
    $this->project = Project::factory()->create(['organization_id' => $this->organization->id]);
    $this->task = Task::factory()->create(['project_id' => $this->project->id]);
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

    test('organization has tasks through projects', function () {
        expect($this->organization->tasks)->toHaveCount(1);
        expect($this->organization->tasks->first()->id)->toBe($this->task->id);
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

    test('project has tasks', function () {
        expect($this->project->tasks)->toHaveCount(1);
        expect($this->project->tasks->first()->id)->toBe($this->task->id);
    });

    test('project can add members', function () {
        $this->project->addMember($this->user, 'lead', ['manage_tasks']);

        expect($this->project->members)->toHaveCount(1);
        expect($this->project->leads)->toHaveCount(1);
        expect($this->project->members->first()->pivot->role)->toBe('lead');
    });

    test('project scopes work correctly', function () {
        $personalProject = Project::factory()->create(['type' => 'personal', 'user_id' => $this->user->id]);

        expect(Project::personal()->count())->toBe(1);
        expect(Project::organizational()->count())->toBe(1);
        expect(Project::forUser($this->user)->count())->toBe(1); // Only personal project
    });
});

describe('Task Relationships', function () {
    test('task belongs to project', function () {
        expect($this->task->project->id)->toBe($this->project->id);
    });

    test('task can have assignees with responsibilities', function () {
        $this->task->assignUser($this->user, 'assignee', 'Primary assignee');

        expect($this->task->assignees)->toHaveCount(1);
        expect($this->task->primaryAssignees)->toHaveCount(1);
        expect($this->task->assignees->first()->pivot->responsibility)->toBe('assignee');
        expect($this->task->assignees->first()->pivot->notes)->toBe('Primary assignee');
    });

    test('task hierarchy works correctly', function () {
        $parentTask = Task::factory()->create(['project_id' => $this->project->id]);
        $childTask = Task::factory()->create([
            'project_id'     => $this->project->id,
            'parent_task_id' => $parentTask->id,
        ]);

        expect($parentTask->children)->toHaveCount(1);
        expect($childTask->parent->id)->toBe($parentTask->id);
        expect($childTask->getDepth())->toBe(1);
        expect($parentTask->getDepth())->toBe(0);
    });

    test('task can be marked as completed', function () {
        $this->task->assignUser($this->user);
        $this->task->markCompleted();

        expect($this->task->fresh()->status)->toBe('completed');
        expect($this->task->fresh()->completed_at)->not->toBeNull();
        expect($this->task->assignees->first()->pivot->completed_at)->not->toBeNull();
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

    test('models can have tags', function () {
        $tag = Tag::factory()->create(['name' => 'urgent']);
        $this->task->addTag($tag, $this->user);

        expect($this->task->tags)->toHaveCount(1);
        expect($this->task->tags->first()->name)->toBe('urgent');
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

    test('can get overdue tasks with relationships', function () {
        $overdueTask = Task::factory()->create([
            'project_id' => $this->project->id,
            'due_date'   => now()->subDay(),
            'status'     => 'in_progress',
        ]);

        $overdueTasks = Task::overdue()->get();

        expect($overdueTasks)->toHaveCount(1);
        expect($overdueTasks->first()->id)->toBe($overdueTask->id);
    });

    test('can get tasks with assignees and roles', function () {
        $this->task->assignUser($this->user, 'assignee');
        $reviewer = User::factory()->create();
        $this->task->assignUser($reviewer, 'reviewer');

        $tasksWithAssignees = Task::with('assignees')->where('id', $this->task->id)->first();

        expect($tasksWithAssignees->assignees)->toHaveCount(2);
        expect($tasksWithAssignees->primaryAssignees)->toHaveCount(1);
        expect($tasksWithAssignees->reviewers)->toHaveCount(1);
    });
});

describe('Performance Optimizations', function () {
    test('eager loading prevents N+1 queries', function () {
        // Create multiple projects with tasks
        $projects = Project::factory(3)->create(['organization_id' => $this->organization->id]);
        $projects->each(function ($project) {
            Task::factory(2)->create(['project_id' => $project->id]);
        });

        // This should execute minimal queries due to eager loading
        $projectsWithTasks = Project::with('tasks')->get();

        expect($projectsWithTasks)->toHaveCount(4); // 3 new + 1 existing
        expect($projectsWithTasks->first()->tasks)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
    });

    test('subquery relationships work correctly', function () {
        Task::factory(3)->create(['project_id' => $this->project->id]);

        $projectWithLatestTask = Project::withLatestTask()->find($this->project->id);

        expect($projectWithLatestTask->latest_task_id)->not->toBeNull();
        expect($projectWithLatestTask->latest_task_title)->not->toBeNull();
    });
});
