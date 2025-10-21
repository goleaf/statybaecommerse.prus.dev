<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\User;
use DefStudio\SearchableInput\DTO\SearchResult;
use Illuminate\Database\Eloquent\Builder;

final class CustomerSearch
{
    /**
     * @return array<int, SearchResult>
     */
    public static function byEmailPhoneName(string $term, int $limit = 15): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, User> $users */
        $users = self::baseQuery($term)
            ->limit($limit)
            ->get();

        return $users
            ->map(function (User $user): SearchResult {
                /** @var string|null $rawName */
                $rawName = $user->getAttribute('name');
                /** @var string|null $rawEmail */
                $rawEmail = $user->getAttribute('email');
                /** @var string|null $rawPhone */
                $rawPhone = $user->getAttribute('phone');
                /** @var string|null $rawAltPhone */
                $rawAltPhone = $user->getAttribute('phone_number');

                $name = $rawName ?? '';
                $email = $rawEmail ?? '';
                $phone = $rawPhone ?? $rawAltPhone ?? '';
                $label = trim(sprintf('%s <%s>', $name !== '' ? $name : __('Unknown'), $email));

                /** @var int|string|null $identifier */
                $identifier = $user->getKey();
                $result = SearchResult::make((string) ($identifier ?? ''), $label);

                // Normalise customer metadata so the autocomplete component can hydrate consistently.
                return SearchResultPayload::normalise($result, [
                    'customer_id' => $user->getKey(),
                    'email'       => $email,
                    'phone'       => $phone,
                    'name'        => $name,
                ]);
            })
            ->all();
    }

    /**
     * @return Builder<User>
     */
    private static function baseQuery(string $term): Builder
    {
        $search = trim($term);

        return User::query()
            ->select(['id', 'name', 'email', 'phone', 'phone_number'])
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('updated_at');
    }
}
