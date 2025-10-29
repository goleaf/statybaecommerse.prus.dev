<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class UserAttributionObserver
{
    /** @var array<int, string|null> */
    private static array $userNameCache = [];

    /** @var array<int, bool> */
    private static array $userExistsCache = [];

    /** @var array<string, int|null> */
    private static array $systemUserEmailCache = [];

    public function creating(Model $model): void
    {
        $userId = $this->resolveUserId();

        if ($userId === null) {
            return;
        }

        if ($this->shouldSetAttribute($model, 'created_by') && $model->getAttribute('created_by') === null) {
            $model->setAttribute('created_by', $userId);
        }

        if ($this->shouldSetAttribute($model, 'updated_by') && $model->getAttribute('updated_by') === null) {
            $model->setAttribute('updated_by', $userId);
        }

        $userName = $this->resolveUserName($userId);

        if ($userName === null) {
            return;
        }

        $canStoreNames = $this->isStringColumn($model, 'created_by_name');

        if ($this->shouldSetAttribute($model, 'created_by_name')
            && $model->getAttribute('created_by_name') === null
            && $canStoreNames) {
            $model->setAttribute('created_by_name', $userName);
        }

        if ($this->shouldSetAttribute($model, 'updated_by_name')
            && $model->getAttribute('updated_by_name') === null
            && $this->isStringColumn($model, 'updated_by_name')) {
            $model->setAttribute('updated_by_name', $userName);
        }
    }

    public function updating(Model $model): void
    {
        $userId = $this->resolveUserId();

        if ($userId === null) {
            return;
        }

        if ($this->shouldSetAttribute($model, 'updated_by')) {
            $model->setAttribute('updated_by', $userId);
        }

        $userName = $this->resolveUserName($userId);

        if ($userName !== null
            && $this->shouldSetAttribute($model, 'updated_by_name')
            && $this->isStringColumn($model, 'updated_by_name')) {
            $model->setAttribute('updated_by_name', $userName);
        }
    }

    private function resolveUserId(): ?int
    {
        $authenticatedId = Auth::id();

        if ($authenticatedId !== null) {
            return (int) $authenticatedId;
        }

        $configuredId = config('attribution.system_user_id');

        if ($configuredId !== null && $configuredId !== '') {
            $candidateId = (int) $configuredId;

            // Cache the existence check so repeated observer runs avoid redundant queries.
            if (! array_key_exists($candidateId, self::$userExistsCache)) {
                self::$userExistsCache[$candidateId] = User::query()->whereKey($candidateId)->exists();
            }

            // Only return the configured identifier when the backing record exists to prevent
            // foreign key violations during inserts executed before seeders provision the user.
            if (self::$userExistsCache[$candidateId]) {
                return $candidateId;
            }
        }

        $systemUserEmail = config('attribution.system_user_email');

        if (! is_string($systemUserEmail) || $systemUserEmail === '') {
            return null;
        }

        if (! array_key_exists($systemUserEmail, self::$systemUserEmailCache)) {
            self::$systemUserEmailCache[$systemUserEmail] = User::query()
                ->where('email', $systemUserEmail)
                ->value('id');
        }

        return self::$systemUserEmailCache[$systemUserEmail];
    }

    private function resolveUserName(?int $userId): ?string
    {
        if ($userId === null) {
            return null;
        }

        $authenticatedUser = Auth::user();

        if ($authenticatedUser !== null && (int) $authenticatedUser->getAuthIdentifier() === $userId) {
            $name = $authenticatedUser->getAttribute('name');

            return is_string($name) && $name !== '' ? $name : null;
        }

        if (! array_key_exists($userId, self::$userNameCache)) {
            self::$userNameCache[$userId] = User::query()->whereKey($userId)->value('name');
        }

        $cached = self::$userNameCache[$userId];

        return is_string($cached) && $cached !== '' ? $cached : null;
    }

    private function shouldSetAttribute(Model $model, string $attribute): bool
    {
        static $columnCache = [];

        if ($model->isFillable($attribute)) {
            return true;
        }

        $table = $model->getTable();
        $connectionName = $model->getConnectionName() ?? config('database.default');
        $cacheKey = $connectionName . '::' . $table;

        if (! array_key_exists($cacheKey, $columnCache)) {
            $columnCache[$cacheKey] = Schema::connection($connectionName)->getColumnListing($table);
        }

        return in_array($attribute, $columnCache[$cacheKey], true);
    }

    private function isStringColumn(Model $model, string $attribute): bool
    {
        static $typeCache = [];

        $table = $model->getTable();
        $connectionName = $model->getConnectionName() ?? config('database.default');
        $cacheKey = $connectionName . '::' . $table . '::' . $attribute;

        if (! array_key_exists($cacheKey, $typeCache)) {
            try {
                $typeCache[$cacheKey] = Schema::connection($connectionName)->getColumnType($table, $attribute);
            } catch (Throwable) {
                $typeCache[$cacheKey] = null;
            }
        }

        $columnType = $typeCache[$cacheKey];

        // Only attempt to persist the human readable name when the underlying column
        // accepts text. SQLite-backed feature flags expose legacy integer columns
        // named *_by_name that still carry foreign keys, so writing strings would
        // violate referential integrity in the test harness.
        if ($columnType === null) {
            return true;
        }

        return in_array($columnType, ['string', 'text', 'char', 'varchar', 'mediumtext', 'longtext'], true);
    }
}
