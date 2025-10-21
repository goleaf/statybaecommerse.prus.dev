<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $rolesTable = $this->rolesTable();

        if (! Schema::hasColumn($rolesTable, 'permissions_matrix')) {
            Schema::table($rolesTable, function (Blueprint $table): void {
                $table->json('permissions_matrix')->nullable()->after('guard_name');
            });
        }

        $this->backfillPermissionsMatrix();
    }

    public function down(): void
    {
        $rolesTable = $this->rolesTable();

        if (Schema::hasColumn($rolesTable, 'permissions_matrix')) {
            Schema::table($rolesTable, function (Blueprint $table): void {
                $table->dropColumn('permissions_matrix');
            });
        }
    }

    private function backfillPermissionsMatrix(): void
    {
        $definition = $this->abilityMatrixDefinition();

        if ($definition === []) {
            return;
        }

        $permissionsByName = [];
        foreach ($definition as $module => $actions) {
            foreach ($actions as $action => $permission) {
                $permissionsByName[$permission] = [$module, $action];
            }
        }

        $rolesTable = $this->rolesTable();
        $permissionsTable = $this->permissionsTable();
        $pivotTable = $this->pivotTable();
        $roleKey = $this->rolePivotKey();
        $permissionKey = $this->permissionPivotKey();

        DB::table($rolesTable)
            ->orderBy('id')
            ->chunkById(100, function ($roles) use ($definition, $permissionsByName, $pivotTable, $permissionsTable, $rolesTable, $roleKey, $permissionKey): void {
                if ($roles->isEmpty()) {
                    return;
                }

                $roleIds = $roles->pluck('id')->all();

                $pivotRows = DB::table($pivotTable)
                    ->whereIn($roleKey, $roleIds)
                    ->get()
                    ->groupBy($roleKey);

                $permissionIds = $pivotRows
                    ->flatMap(fn ($rows) => $rows->pluck($permissionKey))
                    ->unique()
                    ->values()
                    ->all();

                $permissionNames = [];

                if ($permissionIds !== []) {
                    $permissionNames = DB::table($permissionsTable)
                        ->whereIn('id', $permissionIds)
                        ->pluck('name', 'id')
                        ->all();
                }

                foreach ($roles as $role) {
                    $matrix = $this->blankMatrix($definition);

                    $assigned = $pivotRows->get($role->id, collect());

                    foreach ($assigned as $row) {
                        $permissionId = $row->{$permissionKey} ?? null;

                        if ($permissionId === null) {
                            continue;
                        }

                        $permissionName = $permissionNames[$permissionId] ?? null;

                        if (! is_string($permissionName) || $permissionName === '') {
                            continue;
                        }

                        $matrixKey = $permissionsByName[$permissionName] ?? null;

                        if ($matrixKey === null) {
                            continue;
                        }

                        [$module, $action] = $matrixKey;
                        $matrix[$module][$action] = true;
                    }

                    DB::table($rolesTable)
                        ->where('id', $role->id)
                        ->update(['permissions_matrix' => json_encode($matrix)]);
                }
            });
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function abilityMatrixDefinition(): array
    {
        $configured = config('authorization.abilities', []);
        $matrix = [];

        if (! is_array($configured)) {
            return $matrix;
        }

        foreach ($configured as $module => $actions) {
            if (! is_array($actions)) {
                continue;
            }

            foreach ($actions as $action => $permission) {
                if (! is_string($module) || $module === '') {
                    continue 2;
                }

                if (! is_string($action) || $action === '') {
                    continue;
                }

                if (! is_string($permission) || $permission === '') {
                    continue;
                }

                $matrix[$module][$action] = $permission;
            }
        }

        return $matrix;
    }

    /**
     * @param  array<string, array<string, string>> $definition
     * @return array<string, array<string, bool>>
     */
    private function blankMatrix(array $definition): array
    {
        $matrix = [];

        foreach ($definition as $module => $actions) {
            foreach ($actions as $action => $permission) {
                $matrix[$module][$action] = false;
            }
        }

        return $matrix;
    }

    private function rolesTable(): string
    {
        return (string) (config('permission.table_names.roles') ?? 'roles');
    }

    private function permissionsTable(): string
    {
        return (string) (config('permission.table_names.permissions') ?? 'permissions');
    }

    private function pivotTable(): string
    {
        return (string) (config('permission.table_names.role_has_permissions') ?? 'role_has_permissions');
    }

    private function rolePivotKey(): string
    {
        return (string) (config('permission.column_names.role_pivot_key') ?? 'role_id');
    }

    private function permissionPivotKey(): string
    {
        return (string) (config('permission.column_names.permission_pivot_key') ?? 'permission_id');
    }
};
