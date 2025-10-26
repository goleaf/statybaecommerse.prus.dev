<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\User;
use DefStudio\SearchableInput\DTO\SearchResult;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

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
        $columns = self::userTableColumns();

        $selectable = array_values(array_intersect($columns, [
            'id',
            'name',
            'email',
            'phone',
            'phone_number',
            'updated_at',
        ]));

        if ($selectable === []) {
            $selectable = ['id', 'name', 'email'];
        }

        return User::query()
            ->withoutGlobalScopes()
            ->select($selectable)
            ->when(in_array('is_active', $columns, true), static function (Builder $builder): void {
                $builder->where('is_active', true);
            })
            ->when($search !== '' && self::hasSearchableColumns($columns), function (Builder $builder) use ($search, $columns): void {
                $builder->where(function (Builder $query) use ($search, $columns): void {
                    foreach (self::searchableColumns($columns) as $column) {
                        $query->orWhere($column, 'like', "%{$search}%");
                    }
                });
            })
            ->when(in_array('updated_at', $columns, true), static function (Builder $builder): void {
                $builder->orderByDesc('updated_at');
            });
    }

    /**
     * @return array<int, string>
     */
    private static function userTableColumns(): array
    {
        static $columns;

        if (is_array($columns)) {
            return $columns;
        }

        $model = new User;

        try {
            $columns = $model->getConnection()
                ->getSchemaBuilder()
                ->getColumnListing($model->getTable());
        } catch (Throwable) {
            $columns = [];
        }

        return $columns;
    }

    /**
     * @param  array<int, string> $columns
     * @return array<int, string>
     */
    private static function searchableColumns(array $columns): array
    {
        return array_values(array_intersect($columns, ['name', 'email', 'phone', 'phone_number']));
    }

    /**
     * @param array<int, string> $columns
     */
    private static function hasSearchableColumns(array $columns): bool
    {
        return self::searchableColumns($columns) !== [];
    }
}
