<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Channel;
use DefStudio\SearchableInput\DTO\SearchResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class ChannelSearch
{
    /**
     * @return array<int, SearchResult>
     */
    public static function results(string $term, int $limit = 15): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Channel> $channels */
        $channels = self::query($term)
            ->limit($limit)
            ->get();

        return $channels
            ->map(static function (Channel $channel): SearchResult {
                /** @var int|string|null $identifier */
                $identifier = $channel->getKey();

                $label = self::label($channel);

                $result = SearchResult::make((string) ($identifier ?? ''), $label);

                $result
                    ->withData('channel_id', $channel->getKey())
                    ->withData('name', self::stringValue($channel->getAttribute('name')))
                    ->withData('code', self::stringValue($channel->getAttribute('code')))
                    ->withData('type', self::stringValue($channel->getAttribute('type')));

                return $result;
            })
            ->all();
    }

    public static function label(Channel $channel): string
    {
        /** @var string|null $rawName */
        $rawName = $channel->getAttribute('name');
        /** @var string|null $rawCode */
        $rawCode = $channel->getAttribute('code');
        /** @var string|null $rawType */
        $rawType = $channel->getAttribute('type');

        $name = $rawName ?? '';
        $code = $rawCode ?? '';
        $type = $rawType ?? '';

        $fragments = array_filter([
            $name !== '' ? $name : __('orders.lookups.channel_unknown'),
            $code !== '' ? $code : null,
            $type !== '' ? Str::headline($type) : null,
        ]);

        return implode(' • ', $fragments);
    }

    /**
     * @return Builder<Channel>
     */
    private static function query(string $term): Builder
    {
        $search = trim($term);

        return Channel::query()
            ->select(['id', 'name', 'code', 'type'])
            ->when($search !== '', static function (Builder $builder) use ($search): void {
                $builder->where(static function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            })
            ->orderBy('name');
    }

    private static function stringValue(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}

