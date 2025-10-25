<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\News;
use App\Models\NewsApproval;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class NewsApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_approval_defines_expected_fillable_and_casts(): void
    {
        // Instantiate the model without hitting the database to inspect configuration.
        $model = new NewsApproval;

        // Confirm mass-assignable attributes guard the moderation data we expect.
        self::assertSame([
            'news_id',
            'user_id',
            'decision',
            'notes',
            'decided_at',
        ], $model->getFillable());

        // Ensure the datetime cast is available for convenience helpers on the model.
        $casts = $model->getCasts();

        self::assertSame([
            'news_id'    => 'integer',
            'user_id'    => 'integer',
            'decided_at' => 'datetime',
        ], array_intersect_key($casts, array_flip(['news_id', 'user_id', 'decided_at'])));
    }

    public function test_relationships_resolve_linked_news_and_user(): void
    {
        // Create the related records that a moderator decision will reference.
        $news = News::factory()->create();
        $user = User::factory()->create();

        // Persist a single approval to exercise the relationship accessors.
        $approval = NewsApproval::query()->create([
            'news_id'    => $news->getKey(),
            'user_id'    => $user->getKey(),
            'decision'   => NewsApproval::DECISION_APPROVED,
            'notes'      => 'Looks good to publish.',
            'decided_at' => now()->subHour(),
        ])->load('news', 'user');

        // The inverse relations should hydrate the owning news entry and reviewer.
        self::assertInstanceOf(News::class, $approval->news);
        self::assertTrue($news->is($approval->news));

        self::assertInstanceOf(User::class, $approval->user);
        self::assertTrue($user->is($approval->user));
    }

    public function test_scopes_filter_by_news_user_and_decision(): void
    {
        // Prepare two news items and reviewers to ensure filters discriminate correctly.
        $primaryNews = News::factory()->create();
        $secondaryNews = News::factory()->create();
        $primaryUser = User::factory()->create();
        $secondaryUser = User::factory()->create();

        // Seed a matching approval alongside a few distractors.
        $matching = NewsApproval::query()->create([
            'news_id'    => $primaryNews->getKey(),
            'user_id'    => $primaryUser->getKey(),
            'decision'   => NewsApproval::DECISION_APPROVED,
            'notes'      => null,
            'decided_at' => now()->subMinutes(5),
        ]);

        NewsApproval::query()->create([
            'news_id'    => $secondaryNews->getKey(),
            'user_id'    => $primaryUser->getKey(),
            'decision'   => NewsApproval::DECISION_APPROVED,
            'notes'      => null,
            'decided_at' => now()->subMinutes(10),
        ]);

        NewsApproval::query()->create([
            'news_id'    => $primaryNews->getKey(),
            'user_id'    => $secondaryUser->getKey(),
            'decision'   => NewsApproval::DECISION_APPROVED,
            'notes'      => null,
            'decided_at' => now()->subMinutes(15),
        ]);

        NewsApproval::query()->create([
            'news_id'    => $primaryNews->getKey(),
            'user_id'    => $primaryUser->getKey(),
            'decision'   => NewsApproval::DECISION_RETURNED,
            'notes'      => 'Needs changes.',
            'decided_at' => now()->subMinutes(20),
        ]);

        // Combine the custom scopes to isolate the single matching approval.
        $results = NewsApproval::query()
            ->forNews($primaryNews)
            ->forUser($primaryUser)
            ->withDecision(NewsApproval::DECISION_APPROVED)
            ->pluck('id')
            ->all();

        self::assertSame([$matching->getKey()], $results);
    }

    public function test_decided_between_scope_honours_inclusive_boundaries(): void
    {
        // Establish deterministic timestamps to probe the date filtering behaviour.
        $start = Carbon::now()->subDays(2)->startOfSecond();
        $end = Carbon::now()->addDay()->startOfSecond();

        $news = News::factory()->create();
        $user = User::factory()->create();

        // Approval exactly on the lower boundary should be included.
        $boundary = NewsApproval::query()->create([
            'news_id'    => $news->getKey(),
            'user_id'    => $user->getKey(),
            'decision'   => NewsApproval::DECISION_APPROVED,
            'notes'      => null,
            'decided_at' => $start,
        ]);

        // Approval clearly inside the window should also be returned.
        $inside = NewsApproval::query()->create([
            'news_id'    => $news->getKey(),
            'user_id'    => $user->getKey(),
            'decision'   => NewsApproval::DECISION_RETURNED,
            'notes'      => 'Follow-up required.',
            'decided_at' => $start->copy()->addDay(),
        ]);

        // Records outside the interval must be excluded regardless of direction.
        NewsApproval::query()->create([
            'news_id'    => $news->getKey(),
            'user_id'    => $user->getKey(),
            'decision'   => NewsApproval::DECISION_APPROVED,
            'notes'      => null,
            'decided_at' => $start->copy()->subSecond(),
        ]);

        NewsApproval::query()->create([
            'news_id'    => $news->getKey(),
            'user_id'    => $user->getKey(),
            'decision'   => NewsApproval::DECISION_RETURNED,
            'notes'      => null,
            'decided_at' => $end->copy()->addSecond(),
        ]);

        // Invoke the date-range scope and confirm only the boundary-inclusive records appear.
        $scopedIds = NewsApproval::query()
            ->decidedBetween($start, $end)
            ->orderBy('decided_at')
            ->pluck('id')
            ->all();

        self::assertSame([
            $boundary->getKey(),
            $inside->getKey(),
        ], $scopedIds);
    }
}
