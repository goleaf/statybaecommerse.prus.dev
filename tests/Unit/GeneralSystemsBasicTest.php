<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Project;
use App\Models\User;

describe('General Systems Basic Test', function () {
    it('verifies legacy task/tag models are removed and core comment system remains', function () {
        expect(class_exists('App\\Models\\Task'))->toBeFalse('Task model should be removed');
        expect(class_exists('App\\Models\\Tag'))->toBeFalse('Tag model should be removed');
        expect(class_exists('App\\Models\\Taggable'))->toBeFalse('Taggable model should be removed');

        expect(class_exists(Comment::class))->toBeTrue('Comment model should exist');
        expect(method_exists(Comment::class, 'commentable'))->toBeTrue('Comment model should have commentable method');
        expect(class_exists(Project::class))->toBeTrue('Project model should exist');
        expect(class_exists(User::class))->toBeTrue('User model should exist');
    });
});
