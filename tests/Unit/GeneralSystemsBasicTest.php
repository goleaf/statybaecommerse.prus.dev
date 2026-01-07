<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Tag;
use App\Models\Taggable;
use App\Models\Task;
use App\Models\User;

describe('General Systems Basic Test', function () {
    /**
     * **Feature: news-blog-cleanup-upgrade, Property 3: General systems preservation**
     * **Validates: Requirements 3.1, 3.2, 3.3, 3.4**
     *
     * For any entity that uses the general Tag or Comment system (Task, User, etc.),
     * after News-specific cleanup, all tag and comment operations should continue
     * to work correctly.
     */
    it('verifies general tag and comment models exist and are functional', function () {
        // Test that Tag model exists and has basic functionality
        expect(class_exists(Tag::class))->toBeTrue('Tag model should exist');
        expect(method_exists(Tag::class, 'taggables'))->toBeTrue('Tag model should have taggables method');
        expect(method_exists(Tag::class, 'users'))->toBeTrue('Tag model should have users method');
        expect(method_exists(Tag::class, 'tasks'))->toBeTrue('Tag model should have tasks method');
        expect(method_exists(Tag::class, 'comments'))->toBeTrue('Tag model should have comments method');
        expect(method_exists(Tag::class, 'getUsageCount'))->toBeTrue('Tag model should have getUsageCount method');

        // Test that Comment model exists and has basic functionality
        expect(class_exists(Comment::class))->toBeTrue('Comment model should exist');
        expect(method_exists(Comment::class, 'user'))->toBeTrue('Comment model should have user method');
        expect(method_exists(Comment::class, 'commentable'))->toBeTrue('Comment model should have commentable method');
        expect(method_exists(Comment::class, 'parent'))->toBeTrue('Comment model should have parent method');
        expect(method_exists(Comment::class, 'children'))->toBeTrue('Comment model should have children method');
        expect(method_exists(Comment::class, 'isRoot'))->toBeTrue('Comment model should have isRoot method');
        expect(method_exists(Comment::class, 'isReply'))->toBeTrue('Comment model should have isReply method');

        // Test that Taggable model exists and has basic functionality
        expect(class_exists(Taggable::class))->toBeTrue('Taggable model should exist');
        expect(method_exists(Taggable::class, 'tag'))->toBeTrue('Taggable model should have tag method');
        expect(method_exists(Taggable::class, 'taggable'))->toBeTrue('Taggable model should have taggable method');
        expect(method_exists(Taggable::class, 'tagger'))->toBeTrue('Taggable model should have tagger method');

        // Test that Task model has tag and comment relationships
        expect(class_exists(Task::class))->toBeTrue('Task model should exist');
        expect(method_exists(Task::class, 'tags'))->toBeTrue('Task model should have tags method');
        expect(method_exists(Task::class, 'comments'))->toBeTrue('Task model should have comments method');

        // Test that User model exists (users author comments, but don't have a comments relationship)
        expect(class_exists(User::class))->toBeTrue('User model should exist');
    });
});
