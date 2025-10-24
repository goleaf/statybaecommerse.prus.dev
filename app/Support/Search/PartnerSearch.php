<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Partner;
use App\Support\Filament\SearchableComponentHelper;
use DefStudio\SearchableInput\DTO\SearchResult;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class PartnerSearch
{
    /**
     * @return array<int, SearchResult>
     */
    public static function results(string $term, int $limit = 15): array
    {
        /** @var EloquentCollection<int, Partner> $partners */
        $partners = self::baseQuery($term)
            ->limit($limit)
            ->get();

        return $partners
            ->map(static fn (Partner $partner): SearchResult => self::toResult($partner))
            ->all();
    }

    public static function label(Partner $partner): string
    {
        $code = self::stringValue($partner->getAttribute('code'));
        $name = self::stringValue($partner->getAttribute('name'));
        $email = self::stringValue($partner->getAttribute('contact_email'));

        $segments = array_filter([
            $code !== '' ? sprintf('[%s]', $code) : null,
            $name !== '' ? $name : null,
            $email !== '' ? sprintf('<%s>', $email) : null,
        ]);

        return trim(implode(' ', $segments));
    }

    public static function hydrateComponent(SearchableInput $component, int|string|null $state): void
    {
        SearchableComponentHelper::hydrate(
            $component,
            $state,
            static function (int|string $identifier): ?Partner {
                if ($identifier === '') {
                    return null;
                }

                return Partner::query()
                    ->select(['id', 'name', 'code', 'contact_email'])
                    ->find($identifier);
            },
            static function (Partner $partner): array {
                $result = self::toResult($partner);
                $payload = SearchResultPayload::hydrate($result)['payload'];

                return [
                    'value'   => $result->value(),
                    'label'   => $result->label(),
                    'payload' => $payload,
                ];
            },
        );
    }

    private static function baseQuery(string $term): Builder
    {
        $search = trim($term);

        return Partner::query()
            ->select(['id', 'name', 'code', 'contact_email'])
            ->when($search !== '', static function (Builder $builder) use ($search): void {
                $builder->where(static function (Builder $query) use ($search): void {
                    $like = "%{$search}%";

                    $query
                        ->where('name', 'like', $like)
                        ->orWhere('code', 'like', $like)
                        ->orWhere('contact_email', 'like', $like);
                });
            })
            ->orderBy('name');
    }

    private static function toResult(Partner $partner): SearchResult
    {
        $identifier = (string) ($partner->getKey() ?? '');
        $result = SearchResult::make($identifier, self::label($partner));

        return SearchResultPayload::normalise($result, [
            'partner_id' => $partner->getKey(),
            'name'       => self::stringValue($partner->getAttribute('name')),
            'code'       => self::stringValue($partner->getAttribute('code')),
            'email'      => self::stringValue($partner->getAttribute('contact_email')),
        ]);
    }

    private static function stringValue(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
