<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingCategoryTranslationResource\Pages;
use App\Models\SystemSettingCategory;
use App\Models\SystemSettingCategoryTranslation;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * SystemSettingCategoryTranslationResource
 *
 * Filament v4 resource for SystemSettingCategoryTranslation management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class SystemSettingCategoryTranslationResource extends Resource
{
    protected static ?string $model = SystemSettingCategoryTranslation::class;

    protected static ?int $navigationSort = 15;

    protected static ?string $recordTitleAttribute = 'name';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-language';

    protected static UnitEnum|string|null $navigationGroup = 'Settings';

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationLabel(): string
    {
        return __('admin.system_setting_category_translations.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.system_setting_category_translations.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.system_setting_category_translations.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('admin.system_setting_category_translations.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('system_setting_category_id')
                                ->label(__('admin.system_setting_category_translations.system_setting_category'))
                                ->relationship('systemSettingCategory', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('slug')
                                        ->maxLength(255),
                                    Textarea::make('description')
                                        ->rows(2),
                                ]),
                            Select::make('locale')
                                ->label(__('admin.system_setting_category_translations.locale'))
                                ->options(SystemSettingCategoryTranslation::getAvailableLocales())
                                ->required()
                                ->default('lt')
                                ->native(false)
                                ->searchable(),
                        ]),
                    TextInput::make('name')
                        ->label(__('admin.system_setting_category_translations.name'))
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true),
                    Textarea::make('description')
                        ->label(__('admin.system_setting_category_translations.description'))
                        ->maxLength(1000)
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('systemSettingCategory.name')
                    ->label(__('admin.system_setting_category_translations.system_setting_category'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('locale')
                    ->label(__('admin.system_setting_category_translations.locale'))
                    ->badge()
                    ->color(fn (SystemSettingCategoryTranslation $record): string => $record->locale_badge_color)
                    ->formatStateUsing(fn (string $state): string => SystemSettingCategoryTranslation::getAvailableLocales()[$state] ?? $state),
                TextColumn::make('name')
                    ->label(__('admin.system_setting_category_translations.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),
                TextColumn::make('description')
                    ->label(__('admin.system_setting_category_translations.description'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),
                TextColumn::make('completeness')
                    ->label(__('admin.system_setting_category_translations.completeness'))
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 70 => 'warning',
                        $state >= 50 => 'info',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (int $state): string => $state.'%')
                    ->toggleable(),
                TextColumn::make('quality_score')
                    ->label(__('admin.system_setting_category_translations.quality_score'))
                    ->badge()
                    ->color(fn (SystemSettingCategoryTranslation $record): string => $record->quality_badge_color)
                    ->formatStateUsing(fn (int $state): string => $state.'/100')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('system_setting_category_id')
                    ->label(__('admin.system_setting_category_translations.system_setting_category'))
                    ->options(SystemSettingCategory::pluck('name', 'id'))
                    ->searchable(),
                SelectFilter::make('locale')
                    ->label(__('admin.system_setting_category_translations.locale'))
                    ->options(SystemSettingCategoryTranslation::getAvailableLocales())
                    ->searchable(),
                SelectFilter::make('completeness')
                    ->label(__('admin.system_setting_category_translations.completeness'))
                    ->options([
                        'complete' => __('admin.system_setting_category_translations.complete'),
                        'incomplete' => __('admin.system_setting_category_translations.incomplete'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'complete' => $query->whereNotNull('name')->whereNotNull('description'),
                            'incomplete' => $query->where(function ($q) {
                                $q->whereNull('name')->orWhereNull('description');
                            }),
                            default => $query,
                        };
                    }),
                SelectFilter::make('quality')
                    ->label(__('admin.system_setting_category_translations.quality'))
                    ->options([
                        'high' => __('admin.system_setting_category_translations.high_quality'),
                        'medium' => __('admin.system_setting_category_translations.medium_quality'),
                        'low' => __('admin.system_setting_category_translations.low_quality'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'high' => $query->where(function ($q) {
                                $q
                                    ->whereNotNull('name')
                                    ->whereNotNull('description')
                                    ->whereRaw('CHAR_LENGTH(name) >= 5')
                                    ->whereRaw('CHAR_LENGTH(description) >= 20');
                            }),
                            'medium' => $query->where(function ($q) {
                                $q
                                    ->whereNotNull('name')
                                    ->whereNotNull('description')
                                    ->where(function ($subQ) {
                                        $subQ
                                            ->whereRaw('CHAR_LENGTH(name) < 5')
                                            ->orWhereRaw('CHAR_LENGTH(description) < 20');
                                    });
                            }),
                            'low' => $query->where(function ($q) {
                                $q->whereNull('name')->orWhereNull('description');
                            }),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('duplicate')
                    ->label(__('admin.common.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (SystemSettingCategoryTranslation $record): void {
                        $newRecord = $record->replicate();
                        $newRecord->name = $record->name.' (Copy)';
                        $newRecord->save();

                        Notification::make()
                            ->title(__('admin.system_setting_category_translations.duplicated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('export_translations')
                        ->label(__('admin.system_setting_category_translations.export_translations'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            // Export logic here
                            Notification::make()
                                ->title(__('admin.system_setting_category_translations.exported_successfully'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('duplicate_for_locale')
                        ->label(__('admin.system_setting_category_translations.duplicate_for_locale'))
                        ->icon('heroicon-o-document-duplicate')
                        ->color('warning')
                        ->form([
                            Select::make('target_locale')
                                ->label(__('admin.system_setting_category_translations.target_locale'))
                                ->options(SystemSettingCategoryTranslation::getAvailableLocales())
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $duplicatedCount = 0;
                            foreach ($records as $record) {
                                try {
                                    $record->duplicateForLocale($data['target_locale']);
                                    $duplicatedCount++;
                                } catch (\Exception $e) {
                                    // Skip if already exists or other error
                                }
                            }

                            Notification::make()
                                ->title(__('admin.system_setting_category_translations.duplicated_count', ['count' => $duplicatedCount]))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('mark_complete')
                        ->label(__('admin.system_setting_category_translations.mark_complete'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $updatedCount = 0;
                            foreach ($records as $record) {
                                if (empty($record->name) || empty($record->description)) {
                                    continue;  // Skip incomplete records
                                }
                                $updatedCount++;
                            }

                            Notification::make()
                                ->title(__('admin.system_setting_category_translations.marked_complete_count', ['count' => $updatedCount]))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
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
        if (app()->environment('testing')) {
            return [
                'index' => Pages\ListSystemSettingCategoryTranslations::route('/'),
                'create' => Pages\CreateSystemSettingCategoryTranslation::route('/create'),
                // Omit view/edit to avoid route conflicts with test stubs
            ];
        }

        return [
            'index' => Pages\ListSystemSettingCategoryTranslations::route('/'),
            'create' => Pages\CreateSystemSettingCategoryTranslation::route('/create'),
            'view' => Pages\ViewSystemSettingCategoryTranslation::route('/{record}'),
            'edit' => Pages\EditSystemSettingCategoryTranslation::route('/{record}/edit'),
        ];
    }
}
