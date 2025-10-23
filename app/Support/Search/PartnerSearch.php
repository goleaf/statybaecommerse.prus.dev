<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Partner;
use DefStudio\SearchableInput\DTO\SearchResult;
use Illuminate\Database\Eloquent\Builder;

final class PartnerSearch
{
    /**
     * @return array<int, SearchResult>
     */
    public static function results(string $term, int $limit = 15): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Partner> $partners */
        $partners = self::query($term)
            ->limit($limit)
            ->get();

        return $partners
            ->map(static function (Partner $partner): SearchResult {
                /** @var int|string|null $identifier */
                $identifier = $partner->getKey();

                $label = self::label($partner);

                $result = SearchResult::make((string) ($identifier ?? ''), $label);

                $result
                    ->withData('partner_id', $partner->getKey())
                    ->withData('name', self::stringValue($partner->getAttribute('name')))
                    ->withData('code', self::stringValue($partner->getAttribute('code')))
                    ->withData('email', self::stringValue($partner->getAttribute('contact_email')));

                return $result;
            })
            ->all();
    }

    public static function label(Partner $partner): string
    {
        /** @var string|null $rawName */
        $rawName = $partner->getAttribute('name');
        /** @var string|null $rawCode */
        $rawCode = $partner->getAttribute('code');
        /** @var string|null $rawEmail */
        $rawEmail = $partner->getAttribute('contact_email');

        $name = $rawName ?? '';
        $code = $rawCode ?? '';
        $email = $rawEmail ?? '';

        return trim(implode(' • ', array_filter([
            $name !== '' ? $name : __('orders.lookups.partner_unknown'),
            $code !== '' ? $code : null,
            $email !== '' ? $email : null,
        ])));
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

