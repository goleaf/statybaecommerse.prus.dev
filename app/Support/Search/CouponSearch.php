<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Builder;

final class CouponSearch
{
    /**
     * @return array<int, SearchResult>
     */
    public static function byCode(string $term, int $limit = 15): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Coupon> $coupons */
        $coupons = self::baseQuery($term)
            ->limit($limit)
            ->get();

        return $coupons
            ->map(function (Coupon $coupon): SearchResult {
                /** @var string|null $rawCode */
                $rawCode = $coupon->getAttribute('code');
                /** @var string|null $rawName */
                $rawName = $coupon->getAttribute('name');

                $code = $rawCode ?? '';
                $name = $rawName ?? '';
                $label = trim(sprintf('%s — %s', $code, $name));

                /** @var int|string|null $identifier */
                $identifier = $coupon->getKey();
                $result = SearchResult::make((string) ($identifier ?? ''), $label);

                // Keep the coupon identifiers grouped inside the payload for consistent reads.
                return SearchResultPayload::normalise($result, [
                    'coupon_id' => $coupon->getKey(),
                    'code'      => $code,
                    'name'      => $name,
                ]);
            })
            ->all();
    }

    /**
     * @return Builder<Coupon>
     */
    private static function baseQuery(string $term): Builder
    {
        $search = trim($term);

        return Coupon::query()
            ->select(['id', 'code', 'name'])
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('code');
    }
}
