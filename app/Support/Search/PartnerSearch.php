<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Partner;
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
        $partners = Partner::query()
            ->select(['id', 'name', 'code', 'contact_email'])
            ->when(trim($term) !== '', function (Builder $builder) use ($term): void {
                $builder->where(function (Builder $query) use ($term): void {
                    $query
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%")
                        ->orWhere('contact_email', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return $partners
            ->map(static function (Partner $partner): SearchResult {
                /** @var int|string|null $identifier */
                $identifier = $partner->getKey();

                $label = self::label($partner);

                $result = SearchResult::make((string) ($identifier ?? ''), $label);

                // Offer the partner metadata via the payload for downstream automation hooks.
                return SearchResultPayload::normalise($result, [
                    'partner_id' => $partner->getKey(),
                    'name'       => self::stringValue($partner->getAttribute('name')),
                    'code'       => self::stringValue($partner->getAttribute('code')),
                    'email'      => self::stringValue($partner->getAttribute('contact_email')),
                ]);
            })
            ->all();
    }

    public static function label(Partner $partner): string
    {
        /** @var string|null $rawCode */
        $rawCode = $partner->getAttribute('code');
        /** @var string|null $rawName */
        $rawName = $partner->getAttribute('name');
        /** @var string|null $rawEmail */
        $rawEmail = $partner->getAttribute('contact_email');

        $code = $rawCode ?: '—';
        $name = $rawName ?? '';
        $email = $rawEmail ?? '';

        $suffix = $email !== '' ? sprintf('<%s>', $email) : '';

    public static function hydrateComponent(SearchableInput $component, int|string|null $state): void
    {
        if ($state === null || $state === '') {
            SearchableComponentHelper::forget($component);

            return;
        }

        $partner = Partner::query()
            ->select(['id', 'name', 'code', 'contact_email'])
            ->find($state);

        if (! $partner instanceof Partner) {
            return;
        }

        SearchableComponentHelper::apply($component, self::toResult($partner));
    }

    /**
     * @return Builder<Partner>
     */
    private static function query(string $term): Builder
    {
        $search = trim($term);

        return Partner::query()
            ->select(['id', 'name', 'code', 'contact_email'])
            ->when($search !== '', static function (Builder $builder) use ($search): void {
                $builder->where(static function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('contact_email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name');
    }

    private static function stringValue(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
