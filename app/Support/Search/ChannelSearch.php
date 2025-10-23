<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Channel;
use DefStudio\SearchableInput\DTO\SearchResult;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
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
            ->map(static fn (Channel $channel): SearchResult => self::toResult($channel))
            ->all();
    }

    public static function label(Channel $channel): string
    {
        /** @var string|null $rawCode */
        $rawCode = $channel->getAttribute('code');
        /** @var string|null $rawName */
        $rawName = $channel->getAttribute('name');

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

    public static function hydrateComponent(SearchableInput $component, int|string|null $state): void
    {
        if ($state === null || $state === '') {
            SearchableComponentHelper::forget($component);

            return;
        }

        $channel = Channel::query()
            ->select(['id', 'name', 'code', 'type'])
            ->find($state);

        if (! $channel instanceof Channel) {
            return;
        }

        SearchableComponentHelper::apply($component, self::toResult($channel));
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

    private static function toResult(Channel $channel): SearchResult
    {
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
    }
}
