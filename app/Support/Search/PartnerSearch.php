<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Partner;
use DefStudio\SearchableInput\DTO\SearchResult;
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
            ->map(function (Partner $partner): SearchResult {
                $identifier = (string) $partner->getKey();
                $result = SearchResult::make($identifier, self::label($partner));

                return $result
                    ->withData('partner_id', $partner->getKey())
                    ->withData('code', (string) ($partner->getAttribute('code') ?? ''))
                    ->withData('name', (string) ($partner->getAttribute('name') ?? ''))
                    ->withData('contact_email', (string) ($partner->getAttribute('contact_email') ?? ''));
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

        return trim(sprintf('[%s] %s %s', $code, $name, $suffix));
    }
}
