<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\NormalSettingTranslationResource\Pages;
use App\Models\NormalSettingTranslation;
use App\Models\NormalSetting;
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
 * NormalSettingTranslationResource
 *
 * Filament v4 resource for NormalSettingTranslation management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class NormalSettingTranslationResource extends Resource
{
    protected static ?string $model = NormalSettingTranslation::class;
    protected static ?int $navigationSort = 16;
    protected static ?string $recordTitleAttribute = 'display_name';
    protected static ?string $navigationGroup = NavigationGroup::Content;

    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Content;

    public static function getNavigationLabel(): string
    {
        return __('admin.normal_setting_translations.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.normal_setting_translations.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.normal_setting_translations.model_label');
    }

    public static function schema(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaSection::make(__('admin.normal_setting_translations.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('enhanced_setting_id')
                                    ->label(__('admin.normal_setting_translations.enhanced_setting'))
                                    ->options(NormalSetting::pluck('key', 'id'))
                                    ->required()
                                    ->searchable(),

                                Select::make('locale')
                                    ->label(__('admin.normal_setting_translations.locale'))
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

                        TextInput::make('display_name')
                            ->label(__('admin.normal_setting_translations.display_name'))
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label(__('admin.normal_setting_translations.description'))
                            ->maxLength(1000)
                            ->rows(3),

                        Textarea::make('help_text')
                            ->label(__('admin.normal_setting_translations.help_text'))
                            ->maxLength(1000)
                            ->rows(3)
                            ->helperText(__('admin.normal_setting_translations.help_text_help')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('enhancedSetting.key')
                    ->label(__('admin.normal_setting_translations.enhanced_setting'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('locale')
                    ->label(__('admin.normal_setting_translations.locale'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'en' => 'success',
                        'lt' => 'info',
                        'de' => 'warning',
                        'fr' => 'danger',
                        'es' => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('display_name')
                    ->label(__('admin.normal_setting_translations.display_name'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('description')
                    ->label(__('admin.normal_setting_translations.description'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('help_text')
                    ->label(__('admin.normal_setting_translations.help_text'))
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
                SelectFilter::make('enhanced_setting_id')
                    ->label(__('admin.normal_setting_translations.enhanced_setting'))
                    ->options(NormalSetting::pluck('key', 'id'))
                    ->searchable(),

                SelectFilter::make('locale')
                    ->label(__('admin.normal_setting_translations.locale'))
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
            'index' => Pages\ListNormalSettingTranslations::route('/'),
            'create' => Pages\CreateNormalSettingTranslation::route('/create'),
            'view' => Pages\ViewNormalSettingTranslation::route('/{record}'),
            'edit' => Pages\EditNormalSettingTranslation::route('/{record}/edit'),
        ];
    }
}
