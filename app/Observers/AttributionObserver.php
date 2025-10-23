<?php

declare(strict_types=1);

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * AttributionObserver
 *
 * Shared observer that automatically manages created_by and updated_by columns
 * for authenticated users.
 */
final class AttributionObserver
{
    public function creating(Model $model): void
    {
        if (! Auth::check()) {
            return;
        }

        $this->setCreatedBy($model);
        $this->setUpdatedBy($model, force: false);
    }

    public function updating(Model $model): void
    {
        if (! Auth::check()) {
            return;
        }

        $this->setUpdatedBy($model);
    }

    private function setCreatedBy(Model $model): void
    {
        if (! $this->columnExists($model, 'created_by')) {
            return;
        }

        if ($model->getAttribute('created_by')) {
            return;
        }

        $model->forceFill(['created_by' => Auth::id()]);
    }

    private function setUpdatedBy(Model $model, bool $force = true): void
    {
        if (! $this->columnExists($model, 'updated_by')) {
            return;
        }

        if (! $force && $model->getAttribute('updated_by')) {
            return;
        }

        $model->forceFill(['updated_by' => Auth::id()]);
    }

    private function columnExists(Model $model, string $column): bool
    {
        static $cache = [];

        $table = $model->getTable();

        if (! array_key_exists($table, $cache)) {
            $cache[$table] = [];
        }

        if (! array_key_exists($column, $cache[$table])) {
            $cache[$table][$column] = Schema::hasColumn($table, $column);
        }

        return $cache[$table][$column];
    }
}
