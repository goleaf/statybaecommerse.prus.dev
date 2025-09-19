<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\SystemSettingDependencyResource\Pages;
use App\Models\SystemSettingDependency;
use App\Models\SystemSetting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * SystemSettingDependencyResource
 *
 * Filament v4 resource for SystemSettingDependency management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class SystemSettingDependencyResource extends Resource
{
    protected static ?string $model = SystemSettingDependency::class;
    protected static ?int $navigationSort = 12;
    protected static ?string $recordTitleAttribute = 'condition';
    protected static ?string $navigationGroup = NavigationGroup::Settings;

    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Settings;

    public static function getNavigationLabel(): string
    {
        return __('admin.system_setting_dependencies.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.system_setting_dependencies.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.system_setting_dependencies.model_label');
    }

    public static function schema(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaSection::make(__('admin.system_setting_dependencies.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('setting_id')
                                    ->label(__('admin.system_setting_dependencies.setting'))
                                    ->options(SystemSetting::pluck('key', 'id'))
                                    ->required()
                                    ->searchable(),

                                Select::make('depends_on_setting_id')
                                    ->label(__('admin.system_setting_dependencies.depends_on_setting'))
                                    ->options(SystemSetting::pluck('key', 'id'))
                                    ->required()
                                    ->searchable(),

                                Toggle::make('is_active')
                                    ->label(__('admin.system_setting_dependencies.is_active'))
                                    ->default(true),
                            ]),

                        Textarea::make('condition')
                            ->label(__('admin.system_setting_dependencies.condition'))
                            ->rows(5)
                            ->helperText(__('admin.system_setting_dependencies.condition_help')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('setting.key')
                    ->label(__('admin.system_setting_dependencies.setting'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('dependsOn.key')
                    ->label(__('admin.system_setting_dependencies.depends_on_setting'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('condition')
                    ->label(__('admin.system_setting_dependencies.condition'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                IconColumn::make('is_active')
                    ->label(__('admin.system_setting_dependencies.is_active'))
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('setting_id')
                    ->label(__('admin.system_setting_dependencies.setting'))
                    ->options(SystemSetting::pluck('key', 'id'))
                    ->searchable(),

                SelectFilter::make('depends_on_setting_id')
                    ->label(__('admin.system_setting_dependencies.depends_on_setting'))
                    ->options(SystemSetting::pluck('key', 'id'))
                    ->searchable(),

                TernaryFilter::make('is_active')
                    ->label(__('admin.system_setting_dependencies.is_active')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemSettingDependencies::route('/'),
            'create' => Pages\CreateSystemSettingDependency::route('/create'),
            'view' => Pages\ViewSystemSettingDependency::route('/{record}'),
            'edit' => Pages\EditSystemSettingDependency::route('/{record}/edit'),
        ];
    }
}
