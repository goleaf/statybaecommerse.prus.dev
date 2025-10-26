<?php

declare(strict_types=1);

namespace App\Services\Export\Exporters;

use App\Models\Export;
use App\Models\User;
use App\Services\Export\Contracts\Exportable;
use App\Services\Export\ExportColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class UserExport implements Exportable
{
    public function name(): string
    {
        return __('Users Export');
    }

    public function columns(): array
    {
        return [
            'name'          => new ExportColumn('name', __('users.fields.name'), 'name'),
            'email'         => new ExportColumn('email', __('users.fields.email'), 'email'),
            'is_active'     => new ExportColumn('is_active', __('users.fields.is_active'), 'is_active'),
            'last_login_at' => new ExportColumn('last_login_at', __('users.fields.last_login_at'), 'last_login_at'),
            'created_at'    => new ExportColumn('created_at', __('users.fields.created_at'), 'created_at'),
            'orders_count'  => new ExportColumn('orders_count', __('users.fields.orders_count'), 'orders_count'),
        ];
    }

    public function defaultColumns(): array
    {
        return ['name', 'email', 'is_active', 'created_at'];
    }

    public function query(array $options = []): Builder
    {
        return User::query()->withCount('orders');
    }

    public function fileName(Export $export): string
    {
        return 'users-export';
    }

    public function map(Model $model, array $columns): array
    {
        /** @var User $model */
        return collect($columns)
            ->map(fn (ExportColumn $column): string => $column->resolve($model))
            ->values()
            ->all();
    }
}
