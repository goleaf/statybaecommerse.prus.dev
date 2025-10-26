<?php

declare(strict_types=1);

namespace App\Support\ProductHistory;

use App\Support\ListQuery\ListQueryDefinition;
use Illuminate\Database\Eloquent\Builder;

/**
 * Centralises the filtering and sorting configuration shared between the
 * product history API controller and export pipeline so both layers enforce the
 * same allow-list semantics.
 */
final class ProductHistoryListConfiguration
{
    /**
     * Build the list query definition with the supported filters and sorts.
     */
    public static function definition(): ListQueryDefinition
    {
        return new ListQueryDefinition(
            filters: [
                'action' => [
                    'type'     => 'string',
                    'nullable' => true,
                    'column'   => 'product_histories.action',
                ],
                'field_name' => [
                    'type'     => 'string',
                    'nullable' => true,
                    'column'   => 'product_histories.field_name',
                ],
                'user_id' => [
                    'type'     => 'int',
                    'nullable' => true,
                    'column'   => 'product_histories.user_id',
                ],
                'date_from' => [
                    'type'     => 'datetime',
                    'nullable' => true,
                    'column'   => 'product_histories.created_at',
                    'operator' => '>=',
                ],
                'date_to' => [
                    'type'     => 'datetime',
                    'nullable' => true,
                    'column'   => 'product_histories.created_at',
                    'operator' => '<=',
                ],
                'search' => [
                    'type'        => 'string',
                    'nullable'    => true,
                    'allow_empty' => false,
                    'callback'    => [self::class, 'applySearchFilter'],
                ],
            ],
            sortable: [
                'created_at' => [
                    'column'            => 'product_histories.created_at',
                    'default_direction' => 'desc',
                ],
                'action' => [
                    'column' => 'product_histories.action',
                ],
            ],
            defaultSort: 'created_at',
            defaultDirection: 'desc',
            defaultPerPage: 15,
            maxPerPage: 100,
            minPerPage: 1,
        );
    }

    /**
     * Apply the search filter callback for keyword queries.
     */
    public static function applySearchFilter(Builder $builder, string $search): void
    {
        $builder->where(static function (Builder $query) use ($search): void {
            $query->where('product_histories.description', 'like', "%{$search}%")
                ->orWhere('product_histories.action', 'like', "%{$search}%")
                ->orWhere('product_histories.field_name', 'like', "%{$search}%");
        });
    }
}
