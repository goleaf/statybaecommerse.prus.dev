<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Lightweight record of editorial approvals for individual news entries.
 *
 * @property int                        $id
 * @property int                        $news_id
 * @property int                        $user_id
 * @property string                     $decision
 * @property string|null                $notes
 * @property \Illuminate\Support\Carbon $decided_at
 * @property-read News                  $news
 * @property-read User                  $user
 *
 * @method static Builder<self> forNews(int|News $news)
 * @method static Builder<self> forUser(int|User $user)
 * @method static Builder<self> withDecision(string $decision)
 * @method static Builder<self> decidedBetween(DateTimeInterface|string $from, DateTimeInterface|string $to)
 */
final class NewsApproval extends Model
{
    /** @use HasFactory<\Database\Factories\NewsApprovalFactory> */
    use HasFactory;

    use OrdersByName;

    /**
     * Order approval records by their decision string so moderation exports
     * group together similar statuses.
     */
    protected string $nameColumn = 'decision';

    /**
     * Canonical decision labels used throughout moderation tooling.
     */
    public const DECISION_APPROVED = 'approved';

    public const DECISION_RETURNED = 'returned';

    /**
     * @var list<string>
     */
    protected $fillable = ['news_id', 'user_id', 'decision', 'notes', 'decided_at'];

    /**
     * Configure attribute casting for type-safety and convenience helpers.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'news_id'    => 'integer',
            'user_id'    => 'integer',
            'decided_at' => 'datetime',
        ];
    }

    /**
     * Establish the inverse relationship to the moderated news entry.
     *
     * @return BelongsTo<News, self>
     */
    public function news(): BelongsTo
    {
        /** @var BelongsTo<News, self> $relation */
        $relation = $this->belongsTo(News::class);

        return $relation;
    }

    /**
     * Establish the reviewer who issued the decision.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<User, self> $relation */
        $relation = $this->belongsTo(User::class);

        return $relation;
    }

    /**
     * Scope the query to approvals recorded for a specific news item.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeForNews(Builder $query, News|int $news): Builder
    {
        // Resolve the foreign key regardless of whether an identifier or model was supplied.
        $newsId = $news instanceof News ? $news->getKey() : $news;

        return $query->where('news_id', $newsId);
    }

    /**
     * Scope the query to approvals attributed to a particular reviewer.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        // Accept either a model instance or a raw identifier for flexibility in callers.
        $userId = $user instanceof User ? $user->getKey() : $user;

        return $query->where('user_id', $userId);
    }

    /**
     * Scope the query to a specific moderation decision value.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeWithDecision(Builder $query, string $decision): Builder
    {
        // Normalize the comparison to guard against leading/trailing whitespace mismatches.
        return $query->where('decision', trim($decision));
    }

    /**
     * Limit approvals to those whose decision timestamp falls within the provided range.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeDecidedBetween(Builder $query, DateTimeInterface|string $from, DateTimeInterface|string $to): Builder
    {
        // Convert the supplied boundaries into Carbon instances and apply an inclusive window.
        $start = $this->normalizeBoundary($from)->startOfSecond();
        $end = $this->normalizeBoundary($to)->endOfSecond();

        return $query->whereBetween('decided_at', [$start, $end]);
    }

    /**
     * Convert assorted date inputs to Carbon while keeping the public scopes tidy.
     */
    private function normalizeBoundary(DateTimeInterface|string $value): Carbon
    {
        // Early return when a Carbon instance is already supplied.
        if ($value instanceof Carbon) {
            return $value->copy();
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        return Carbon::parse($value);
    }
}
