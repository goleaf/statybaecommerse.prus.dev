<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\SystemSettingTranslationResource\Pages;
use App\Models\SystemSettingTranslation;
use App\Models\SystemSetting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * SystemSettingTranslationResource
 *
 * Filament v4 resource for SystemSettingTranslation management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class SystemSettingTranslationResource extends Resource
{
    protected static ?string $model = SystemSettingTranslation::class;
    protected static ?int $navigationSort = 14;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationGroup = NavigationGroup::Content;

    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Content;

    public static function getNavigationLabel(): string
    {
        return __('admin.system_setting_translations.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.system_setting_translations.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.system_setting_translations.model_label');
    }

    public static function schema(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaSection::make(__('admin.system_setting_translations.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('system_setting_id')
                                    ->label(__('admin.system_setting_translations.system_setting'))
                                    ->options(SystemSetting::pluck('key', 'id'))
                                    ->required()
                                    ->searchable(),

                                Select::make('locale')
                                    ->label(__('admin.system_setting_translations.locale'))
                                    ->options([
                                        'en' => 'English',
                                        'lt' => 'Lithuanian',
                                        'de' => 'German',
                                        'fr' => 'French',
                                        'es' => 'Spanish',
                                    ])
                                    ->required()
                                    ->default('en'),
                            ]),

                        TextInput::make('name')
                            ->label(__('admin.system_setting_translations.name'))
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label(__('admin.system_setting_translations.description'))
                            ->maxLength(1000)
                            ->rows(3),

                        Textarea::make('help_text')
                            ->label(__('admin.system_setting_translations.help_text'))
                            ->maxLength(1000)
                            ->rows(3)
                            ->helperText(__('admin.system_setting_translations.help_text_help')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('systemSetting.key')
                    ->label(__('admin.system_setting_translations.system_setting'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('locale')
                    ->label(__('admin.system_setting_translations.locale'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'en' => 'success',
                        'lt' => 'info',
                        'de' => 'warning',
                        'fr' => 'danger',
                        'es' => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('name')
                    ->label(__('admin.system_setting_translations.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('description')
                    ->label(__('admin.system_setting_translations.description'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('help_text')
                    ->label(__('admin.system_setting_translations.help_text'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('system_setting_id')
                    ->label(__('admin.system_setting_translations.system_setting'))
                    ->options(SystemSetting::pluck('key', 'id'))
                    ->searchable(),

                SelectFilter::make('locale')
                    ->label(__('admin.system_setting_translations.locale'))
                    ->options([
                        'en' => 'English',
                        'lt' => 'Lithuanian',
                        'de' => 'German',
                        'fr' => 'French',
                        'es' => 'Spanish',
                    ]),
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
            ->defaultSort('locale');
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
            'index' => Pages\ListSystemSettingTranslations::route('/'),
            'create' => Pages\CreateSystemSettingTranslation::route('/create'),
            'view' => Pages\ViewSystemSettingTranslation::route('/{record}'),
            'edit' => Pages\EditSystemSettingTranslation::route('/{record}/edit'),
        ];
    }
}
