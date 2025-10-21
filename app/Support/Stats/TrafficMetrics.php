<?php

declare(strict_types=1);

namespace App\Support\Stats;

use App\Models\AnalyticsEvent;
use App\Models\Subscriber;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Flowframe\Trend\Trend;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class TrafficMetrics
{
    private const CACHE_TTL_SECONDS = 60;

    /**
     * @return array{
     *     sessions: int,
     *     conversions: int,
     *     newUsers: int,
     *     activeUsers: int,
     *     newsletterSignups: int,
     *     conversionRate: float
     * }
     */
    public static function forRange(CarbonInterface $from, CarbonInterface $to): array
    {
        [$from, $to] = self::normaliseRange($from, $to);

        $cacheKey = self::cacheKey('traffic.range', $from, $to);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, static function () use ($from, $to): array {
            $sessions = self::sessionsCount($from, $to);
            $conversions = self::conversionsCount($from, $to);
            $newUsers = User::query()->whereBetween('created_at', [$from, $to])->count();
            $activeUsers = User::query()->whereBetween('last_activity_at', [$from, $to])->count();
            $newsletterSignups = Subscriber::query()->whereBetween('subscribed_at', [$from, $to])->count();

            $conversionRate = $sessions > 0 ? ($conversions / $sessions) * 100 : 0.0;

            return [
                'sessions'          => $sessions,
                'conversions'       => $conversions,
                'newUsers'          => $newUsers,
                'activeUsers'       => $activeUsers,
                'newsletterSignups' => $newsletterSignups,
                'conversionRate'    => round($conversionRate, 2),
            ];
        });
    }

    public static function sessionsCount(CarbonInterface $from, CarbonInterface $to): int
    {
        [$from, $to] = self::normaliseRange($from, $to);

        return Cache::remember(self::cacheKey('traffic.sessions', $from, $to), self::CACHE_TTL_SECONDS, static function () use ($from, $to): int {
            return AnalyticsEvent::query()
                ->whereBetween('created_at', [$from, $to])
                ->whereNotNull('session_id')
                ->distinct('session_id')
                ->count('session_id');
        });
    }

    public static function conversionsCount(CarbonInterface $from, CarbonInterface $to): int
    {
        [$from, $to] = self::normaliseRange($from, $to);

        return Cache::remember(self::cacheKey('traffic.conversions', $from, $to), self::CACHE_TTL_SECONDS, static function () use ($from, $to): int {
            return AnalyticsEvent::query()
                ->whereBetween('created_at', [$from, $to])
                ->where('is_conversion', true)
                ->count();
        });
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    public static function sessionsTrend(CarbonInterface $from, CarbonInterface $to): array
    {
        [$from, $to] = self::normaliseRange($from, $to);

        $cacheKey = self::cacheKey('traffic.sessions.trend', $from, $to);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, static function () use ($from, $to): array {
            $diffInDays = $from->diffInDays($to);
            $granularityMethod = $diffInDays > 60 ? 'perMonth' : 'perDay';

            $trend = Trend::query(
                AnalyticsEvent::query()
                    ->whereBetween('created_at', [$from, $to])
                    ->whereNotNull('session_id')
            )
                ->between($from, $to)
                ->{$granularityMethod}()
                ->count();

            $labels = [];
            $values = [];

            foreach ($trend as $value) {
                $labels[] = $diffInDays > 60
                    ? CarbonImmutable::parse($value->date)->isoFormat('MMM YYYY')
                    : CarbonImmutable::parse($value->date)->isoFormat('MMM D');
                $values[] = (int) $value->aggregate;
            }

            return [
                'labels' => $labels,
                'values' => $values,
            ];
        });
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private static function normaliseRange(CarbonInterface $from, CarbonInterface $to): array
    {
        $start = CarbonImmutable::instance($from)->startOfDay();
        $end = CarbonImmutable::instance($to)->endOfDay();

        if ($start->greaterThan($end)) {
            return [$end, $start];
        }

        return [$start, $end];
    }

    private static function cacheKey(string $prefix, CarbonInterface $from, CarbonInterface $to): string
    {
        $start = CarbonImmutable::instance($from)->format('YmdHis');
        $end = CarbonImmutable::instance($to)->format('YmdHis');

        return Str::slug("{$prefix}.{$start}.{$end}");
    }
}
