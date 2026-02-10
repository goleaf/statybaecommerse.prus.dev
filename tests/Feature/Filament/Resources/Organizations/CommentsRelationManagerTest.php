<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\Organizations;

use App\Filament\Resources\Organizations\Pages\EditOrganization;
use App\Filament\Resources\Organizations\RelationManagers\CommentsRelationManager;
use App\Models\AdminUser;
use App\Models\Comment;
use App\Models\Organization;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->admin = AdminUser::factory()->create();
    $this->organization = Organization::factory()->create();
});

it('can list comments', function () {
    $comments = Comment::factory()->count(3)->create([
        'commentable_id' => $this->organization->id,
        'commentable_type' => Organization::class,
        'is_approved' => true, // Comment model has ApprovedScope
    ]);

    actingAs($this->admin, 'admin');

    Livewire::test(CommentsRelationManager::class, [
        'ownerRecord' => $this->organization,
        'pageClass' => EditOrganization::class,
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords($comments);
});

it('can create a comment as an admin user with mapped user', function () {
    $email = 'admin@test.com';
    $user = User::factory()->create(['email' => $email]);
    $admin = AdminUser::factory()->create(['email' => $email]);

    auth()->guard('admin')->login($admin);

    Livewire::test(CommentsRelationManager::class, [
        'ownerRecord' => $this->organization,
        'pageClass' => EditOrganization::class,
    ])
        ->assertSuccessful()
        ->mountTableAction('create')
        ->dump()
        ->fillTableActionForm([
            'content' => 'Admin test comment',
        ])
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('comments', [
        'content' => 'Admin test comment',
        'user_id' => $user->id,
        'commentable_id' => $this->organization->id,
        'commentable_type' => Organization::class,
    ]);
});

it('can create a comment as an admin user without mapped user', function () {
    $admin = AdminUser::factory()->create(['email' => 'no-user@test.com']);

    actingAs($admin, 'admin');

    Livewire::test(CommentsRelationManager::class, [
        'ownerRecord' => $this->organization,
        'pageClass' => EditOrganization::class,
    ])
        ->assertSuccessful()
        ->mountTableAction('create')
        ->fillTableActionForm([
            'content' => 'Admin without user comment',
        ])
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('comments', [
        'content' => 'Admin without user comment',
        'user_id' => null,
        'commentable_id' => $this->organization->id,
        'commentable_type' => Organization::class,
    ]);
});

it('can edit a comment', function () {
    $comment = Comment::factory()->create([
        'commentable_id' => $this->organization->id,
        'commentable_type' => Organization::class,
        'content' => 'Original content',
        'is_approved' => true,
    ]);

    actingAs($this->admin, 'admin');

    Livewire::test(CommentsRelationManager::class, [
        'ownerRecord' => $this->organization,
        'pageClass' => EditOrganization::class,
    ])
        ->assertSuccessful()
        ->mountTableAction('edit', $comment)
        ->fillTableActionForm([
            'content' => 'Updated content',
        ])
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'content' => 'Updated content',
    ]);
});

it('can delete a comment', function () {
    $comment = Comment::factory()->create([
        'commentable_id' => $this->organization->id,
        'commentable_type' => Organization::class,
        'is_approved' => true,
    ]);

    actingAs($this->admin, 'admin');

    Livewire::test(CommentsRelationManager::class, [
        'ownerRecord' => $this->organization,
        'pageClass' => EditOrganization::class,
    ])
        ->assertSuccessful()
        ->callTableAction('delete', $comment)
        ->assertHasNoTableActionErrors();

    $this->assertSoftDeleted('comments', [
        'id' => $comment->id,
    ]);
});