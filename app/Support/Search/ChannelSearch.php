<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Channel;
use DefStudio\SearchableInput\DTO\SearchResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class ChannelSearch
{
    /**
     * @return array<int, SearchResult>
     */
    public static function results(string $term, int $limit = 15): array
    {
        /** @var EloquentCollection<int, Channel> $channels */
        $channels = Channel::query()
            ->select(['id', 'name', 'code'])
            ->when(trim($term) !== '', function (Builder $builder) use ($term): void {
                $builder->where(function (Builder $query) use ($term): void {
                    $query
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return $channels
            ->map(function (Channel $channel): SearchResult {
                $identifier = (string) $channel->getKey();
                $result = SearchResult::make($identifier, self::label($channel));

                return $result
                    ->withData('channel_id', $channel->getKey())
                    ->withData('code', (string) ($channel->getAttribute('code') ?? ''))
                    ->withData('name', (string) ($channel->getAttribute('name') ?? ''));
            })
            ->all();
    }

    public static function label(Channel $channel): string
    {
        /** @var string|null $rawCode */
        $rawCode = $channel->getAttribute('code');
        /** @var string|null $rawName */
        $rawName = $channel->getAttribute('name');

        return trim(sprintf('[%s] %s', $rawCode ?: '—', $rawName ?? ''));
    }
}
