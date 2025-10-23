<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\Role;
use App\Support\Authorization\AuthorizationMatrix;
use App\Support\Forms\MatrixFactory;
use BackedEnum;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use UnitEnum;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
final class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?int $navigationSort = 19;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    public static function shouldRegisterNavigation(): bool
    {
        return AuthorizationMatrix::check('roles', 'viewAny');
    }

    public static function canViewAny(): bool
    {
        return AuthorizationMatrix::check('roles', 'viewAny');
    }

    public static function canView(Model $record): bool
    {
        return AuthorizationMatrix::check('roles', 'view');
    }

    public static function canCreate(): bool
    {
        return AuthorizationMatrix::check('roles', 'create');
    }

    public static function canEdit(Model $record): bool
    {
        return AuthorizationMatrix::check('roles', 'update');
    }

    public static function canDelete(Model $record): bool
    {
        return AuthorizationMatrix::check('roles', 'delete');
    }

    public static function canForceDelete(Model $record): bool
    {
        return AuthorizationMatrix::check('roles', 'delete');
    }

    public static function canRestore(Model $record): bool
    {
        return AuthorizationMatrix::check('roles', 'update');
    }

    public static function getNavigationLabel(): string
    {
        return __('roles.navigation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('roles.plural');
    }

    public static function getModelLabel(): string
    {
        return __('roles.single');
    }

    public static function form(Schema $form): Schema
    {
        return $schema
            ->schema([
                Section::make(__('roles.sections.general'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('roles.fields.name'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('guard_name')
                            ->label(__('roles.fields.guard_name'))
                            ->options(self::guardOptions())
                            ->default(self::defaultGuardName())
                            ->required()
                            ->disabled(fn (string $context): bool => $context === 'edit')
                            ->native(false),
                    ])
                    ->columns(2),
                MatrixFactory::permissions(
                    definition: self::matrixDefinition(),
                    moduleLabelResolver: fn (string $module): string => self::moduleLabel($module),
                    abilityLabelResolver: fn (string $ability): string => self::abilityLabel($ability),
                    sectionLabel: __('roles.sections.permissions'),
                ),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('roles.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guard_name')
                    ->label(__('roles.fields.guard_name'))
                    ->sortable(),
                TextColumn::make('permissions_count')
                    ->label(__('roles.fields.permissions_count'))
                    ->counts('permissions')
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit'   => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function matrixDefinition(): array
    {
        $configured = config('authorization.abilities', []);
        $definition = [];

        if (! is_array($configured)) {
            return $definition;
        }

        foreach ($configured as $module => $actions) {
            if (! is_string($module) || $module === '' || ! is_array($actions)) {
                continue;
            }

            foreach ($actions as $action => $permission) {
                if (! is_string($action) || $action === '') {
                    continue;
                }

                if (! is_string($permission) || $permission === '') {
                    continue;
                }

                $definition[$module][$action] = $permission;
            }
        }

        return $definition;
    }

    /**
     * @return array<string, array<string, bool>>
     */
    public static function normalizedMatrix(mixed $state): array
    {
        $normalized = [];

        if (! is_array($state)) {
            $state = [];
        }

        foreach (self::matrixDefinition() as $module => $actions) {
            foreach (array_keys($actions) as $action) {
                $normalized[$module][$action] = (bool) data_get($state, sprintf('%s.%s', $module, $action), false);
            }
        }

        return $normalized;
    }

    /**
     * @param  array<string, array<string, mixed>> $matrix
     * @return array<int, string>
     */
    public static function permissionsFromMatrix(array $matrix): array
    {
        $permissions = [];

        foreach (self::matrixDefinition() as $module => $actions) {
            foreach ($actions as $action => $permission) {
                if (! empty($matrix[$module][$action])) {
                    $permissions[] = $permission;
                }
            }
        }

        return array_values(array_unique($permissions));
    }

    public static function syncSpatiePermissions(Role $role): void
    {
        $matrix = self::normalizedMatrix($role->permissions_matrix ?? []);

        $role->permissions()->sync(self::permissionsFromMatrix($matrix));
    }

    public static function moduleLabel(string $module): string
    {
        $key = sprintf('roles.modules.%s', $module);
        $translation = __($key);

        return $translation !== $key ? $translation : Str::headline($module);
    }

    public static function abilityLabel(string $ability): string
    {
        $key = sprintf('roles.abilities.%s', $ability);
        $translation = __($key);

        return $translation !== $key ? $translation : Str::headline($ability);
    }

    /**
     * @return array<string, string>
     */
    public static function guardOptions(): array
    {
        $guards = AuthorizationMatrix::guardNames();

        if ($guards === []) {
            $guards = ['web'];
        }

        $options = [];

        foreach ($guards as $guard) {
            if (! is_string($guard) || $guard === '') {
                continue;
            }

            $options[$guard] = Str::headline($guard);
        }

        return $options;
    }

    public static function defaultGuardName(): string
    {
        $options = array_keys(self::guardOptions());

        return $options[0] ?? 'web';
    }
}