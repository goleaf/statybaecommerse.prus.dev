<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use App\Models\Comment;
use App\Models\File;
use Illuminate\Database\Seeder;

final class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have some users to act as authors for comments/files
        $authors = User::all();
        if ($authors->isEmpty()) {
            $authors = User::factory(5)->create();
        }

        // Create 10 organizations
        Organization::factory(10)
            ->create()
            ->each(function (Organization $organization) use ($authors) {
                // Add 5-10 users to each organization
                $users = User::factory(rand(5, 10))->create();
                
                $users->each(function ($user, $index) use ($organization) {
                    $role = match ($index) {
                        0 => 'owner',
                        1 => 'admin',
                        default => 'member',
                    };
                    
                    $organization->addUser($user, $role, ['read', 'write']);
                });

                // Create 3-5 projects for each organization
                Project::factory(rand(3, 5))->create([
                    'organization_id' => $organization->id,
                    'type' => 'organizational',
                ])->each(function (Project $project) use ($users) {
                    // Add some organization users as project members
                    $projectUsers = $users->random(min(rand(2, 4), $users->count()));
                    $projectUsers->each(function ($user, $index) use ($project) {
                        $role = $index === 0 ? 'lead' : 'member';
                        $project->addMember($user, $role, ['read', 'write']);
                    });
                });

                // Add 2-4 comments to each organization
                if (class_exists(Comment::class)) {
                    Comment::factory(rand(2, 4))->create([
                        'commentable_id' => $organization->id,
                        'commentable_type' => $organization->getMorphClass(),
                        'user_id' => $authors->random()->id,
                    ]);
                }

                // Add 1-3 files to each organization
                if (class_exists(File::class)) {
                    File::factory(rand(1, 3))->create([
                        'fileable_id' => $organization->id,
                        'fileable_type' => $organization->getMorphClass(),
                        'uploaded_by' => $authors->random()->id,
                    ]);
                }
            });
    }
}
