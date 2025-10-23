<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('database.default', 'sqlite');
        $schema = Schema::connection($connection);

        $this->ensurePermissionTables($schema);
        $this->ensureAttributesTables($schema, $connection);
        $this->ensureVariantPivotTable($schema);
        $this->ensureUsersTable($schema);
    }

    public function down(): void
    {
        $connection = config('database.default', 'sqlite');
        $schema = Schema::connection($connection);

        $tables = config('permission.table_names');
        $schema->dropIfExists($tables['model_has_permissions'] ?? 'model_has_permissions');
        $schema->dropIfExists($tables['model_has_roles'] ?? 'model_has_roles');
        $schema->dropIfExists($tables['role_has_permissions'] ?? 'role_has_permissions');
        $schema->dropIfExists($tables['permissions'] ?? 'permissions');
        $schema->dropIfExists($tables['roles'] ?? 'roles');

        $schema->dropIfExists('product_variant_attributes');
        $schema->dropIfExists('users');
        $schema->dropIfExists('attribute_values');
        $schema->dropIfExists('attributes');
    }

    /**
     * Provision the Spatie permission tables with the expected composite keys so
     * Filament resources and policies can resolve role/permission checks during
     * unit tests without the full production migration history.
     */
    private function ensurePermissionTables(Builder $schema): void
    {
        $tables = config('permission.table_names');
        $columns = config('permission.column_names');
        $teamsEnabled = (bool) config('permission.teams', false);
        $teamKey = $columns['team_foreign_key'] ?? 'team_id';
        $modelKey = $columns['model_morph_key'] ?? 'model_id';

        $permissions = $tables['permissions'] ?? 'permissions';
        if (! $schema->hasTable($permissions)) {
            $schema->create($permissions, function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                $table->unique(['name', 'guard_name']);
            });
        }

        $roles = $tables['roles'] ?? 'roles';
        if (! $schema->hasTable($roles)) {
            $schema->create($roles, function (Blueprint $table) use ($teamsEnabled, $teamKey): void {
                $table->id();
                if ($teamsEnabled) {
                    $table->unsignedBigInteger($teamKey)->nullable();
                    $table->index($teamKey, 'roles_'.$teamKey.'_index');
                }
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                $table->unique(['name', 'guard_name']);
            });
        }

        $modelHasPermissions = $tables['model_has_permissions'] ?? 'model_has_permissions';
        if (! $schema->hasTable($modelHasPermissions)) {
            $schema->create($modelHasPermissions, function (Blueprint $table) use ($permissions, $teamsEnabled, $teamKey, $modelKey): void {
                $table->unsignedBigInteger('permission_id');
                if ($teamsEnabled) {
                    $table->unsignedBigInteger($teamKey)->nullable();
                    $table->index($teamKey, 'model_has_permissions_'.$teamKey.'_index');
                }
                $table->string('model_type');
                $table->unsignedBigInteger($modelKey);
                $table->index([$modelKey, 'model_type'], 'model_has_permissions_model_id_model_type_index');
                $table->foreign('permission_id')->references('id')->on($permissions)->cascadeOnDelete();
                $table->primary($this->compilePermissionPrimaryKeys($modelKey, $teamsEnabled));
            });
        }

        $modelHasRoles = $tables['model_has_roles'] ?? 'model_has_roles';
        if (! $schema->hasTable($modelHasRoles)) {
            $schema->create($modelHasRoles, function (Blueprint $table) use ($roles, $teamsEnabled, $teamKey, $modelKey): void {
                $table->unsignedBigInteger('role_id');
                if ($teamsEnabled) {
                    $table->unsignedBigInteger($teamKey)->nullable();
                    $table->index($teamKey, 'model_has_roles_'.$teamKey.'_index');
                }
                $table->string('model_type');
                $table->unsignedBigInteger($modelKey);
                $table->index([$modelKey, 'model_type'], 'model_has_roles_model_id_model_type_index');
                $table->foreign('role_id')->references('id')->on($roles)->cascadeOnDelete();
                $table->primary($this->compilePermissionPrimaryKeys($modelKey, $teamsEnabled));
            });
        }

        $roleHasPermissions = $tables['role_has_permissions'] ?? 'role_has_permissions';
        if (! $schema->hasTable($roleHasPermissions)) {
            $schema->create($roleHasPermissions, function (Blueprint $table) use ($roles, $permissions): void {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');
                $table->foreign('permission_id')->references('id')->on($permissions)->cascadeOnDelete();
                $table->foreign('role_id')->references('id')->on($roles)->cascadeOnDelete();
                $table->primary(['permission_id', 'role_id']);
            });
        }
    }

    /**
     * Guarantee the attribute and attribute value tables exist with the columns
     * required by factories and Filament resources when running against SQLite.
     */
    private function ensureAttributesTables(Builder $schema, string $connection): void
    {
        if (! $schema->hasTable('attributes')) {
            $schema->create('attributes', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('type')->default('text');
                $table->text('description')->nullable();
                $table->json('validation_rules')->nullable();
                $table->text('default_value')->nullable();
                $table->boolean('is_required')->default(false);
                $table->boolean('is_filterable')->default(false);
                $table->boolean('is_searchable')->default(false);
                $table->boolean('is_visible')->default(true);
                $table->boolean('is_editable')->default(true);
                $table->boolean('is_sortable')->default(false);
                $table->integer('sort_order')->default(0);
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('group_name')->nullable();
                $table->string('icon')->nullable();
                $table->string('color')->nullable();
                $table->unsignedInteger('min_length')->nullable();
                $table->unsignedInteger('max_length')->nullable();
                $table->decimal('min_value', 12, 4)->nullable();
                $table->decimal('max_value', 12, 4)->nullable();
                $table->decimal('step_value', 12, 4)->nullable();
                $table->string('placeholder')->nullable();
                $table->text('help_text')->nullable();
                $table->json('meta_data')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            $this->ensureAttributeColumns($connection, 'attributes', [
                'is_active' => static function (Blueprint $table): void {
                    $table->boolean('is_active')->default(true)->after('is_enabled');
                },
                'validation_rules' => static function (Blueprint $table): void {
                    $table->json('validation_rules')->nullable()->after('description');
                },
                'meta_data' => static function (Blueprint $table): void {
                    $table->json('meta_data')->nullable()->after('help_text');
                },
            ]);
        }

        if (! $schema->hasTable('attribute_values')) {
            $schema->create('attribute_values', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
                $table->string('value');
                $table->string('slug')->nullable();
                $table->string('color_code')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->string('display_value')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['attribute_id', 'sort_order']);
            });
        } else {
            $this->ensureAttributeColumns($connection, 'attribute_values', [
                'is_active' => static function (Blueprint $table): void {
                    $table->boolean('is_active')->default(true)->after('is_enabled');
                },
                'is_default' => static function (Blueprint $table): void {
                    $table->boolean('is_default')->default(false)->after('is_active');
                },
                'display_value' => static function (Blueprint $table): void {
                    $table->string('display_value')->nullable()->after('value');
                },
                'metadata' => static function (Blueprint $table): void {
                    $table->json('metadata')->nullable()->after('display_value');
                },
            ]);
        }
    }

    /**
     * Create the product variant attribute pivot table used by the attribute matrix
     * synchronisation service when running Filament feature tests.
     */
    private function ensureVariantPivotTable(Builder $schema): void
    {
        if ($schema->hasTable('product_variant_attributes')) {
            return;
        }

        $schema->create('product_variant_attributes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('variant_id');
            $table->unsignedBigInteger('attribute_id');
            $table->unsignedBigInteger('attribute_value_id');
            $table->timestamps();
            $table->unique(['variant_id', 'attribute_id', 'attribute_value_id'], 'variant_attribute_unique');
        });
    }

    /**
     * Ensure a minimal users table exists for factories that rely on admin accounts in tests.
     */
    private function ensureUsersTable(Builder $schema): void
    {
        if ($schema->hasTable('users')) {
            return;
        }

        $schema->create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('preferred_locale', 5)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_admin')->default(false);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Apply the given column definitions when they do not already exist on the target table.
     *
     * @param array<string, callable(Blueprint): void> $columns
     */
    private function ensureAttributeColumns(string $connection, string $table, array $columns): void
    {
        foreach ($columns as $column => $callback) {
            if (Schema::connection($connection)->hasColumn($table, $column)) {
                continue;
            }

            Schema::connection($connection)->table($table, static function (Blueprint $table) use ($callback): void {
                $callback($table);
            });
        }
    }

    /**
     * Determine the composite key definition for the Spatie pivot tables while respecting teams.
     *
     * @return array<int, string>
     */
    private function compilePermissionPrimaryKeys(string $modelKey, bool $teamsEnabled): array
    {
        $keys = ['permission_id'];

        if ($teamsEnabled) {
            $keys[] = config('permission.column_names.team_foreign_key', 'team_id');
        }

        $keys[] = $modelKey;
        $keys[] = 'model_type';

        return $keys;
    }
};
