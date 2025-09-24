<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class TrackedScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'is_tracked')) {
            $builder->where('is_tracked', true);
        }
    }
}
