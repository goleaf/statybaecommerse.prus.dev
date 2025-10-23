<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\LegalResource\Pages;
use App\Filament\Resources\LegalResource\RelationManagers\TranslationsRelationManager;
use App\Models\Legal;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\PublishedScope;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class LegalResource extends Resource
{
    protected static ?string $model = Legal::class;

    public static function getNavigationLabel(): string
    {
        return __('legal.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('legal.plural');
    }

    public static function getModelLabel(): string
    {
        return __('legal.single');
    }

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-scale';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('legal.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('key')
                                    ->label(__('legal.key'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText(__('legal.key_help')),
                                Select::make('type')
                                    ->label(__('legal.type'))
                                    ->options(Legal::getTypes())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_enabled')
                                    ->label(__('legal.is_enabled'))
                                    ->default(true),
                                Toggle::make('is_required')
                                    ->label(__('legal.is_required'))
                                    ->default(false),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('sort_order')
                                    ->label(__('legal.sort_order'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0),
                                DateTimePicker::make('published_at')
                                    ->label(__('legal.published_at'))
                                    ->seconds(false)
                                    ->native(false),
                            ]),
                        KeyValue::make('meta_data')
                            ->label(__('legal.meta_data'))
                            ->helperText(__('legal.meta_data_help'))
                            ->keyLabel(__('legal.key'))
                            ->valueLabel(__('legal.content'))
                            ->columnSpanFull()
                            ->nullable(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(__('legal.key'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('legal.title'))
                    ->state(fn (Legal $record): string => $record->getTranslatedTitle() ?? __('legal.untitled_document'))
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('type')
                    ->label(__('legal.type'))
                    ->formatStateUsing(fn (string $state): string => Legal::getTypes()[$state] ?? $state)
                    ->colors([
                        'success' => 'privacy_policy',
                        'warning' => 'terms_of_use',
                        'info' => 'refund_policy',
                        'primary' => 'shipping_policy',
                        'gray' => 'legal_document',
                    ])
                    ->searchable(),
                BadgeColumn::make('status')
                    ->label(__('legal.status'))
                    ->colors([
                        'danger' => 'disabled',
                        'warning' => 'draft',
                        'success' => 'published',
                    ])
                    ->state(fn (Legal $record): string => $record->status)
                    ->sortable(),
                IconColumn::make('is_enabled')
                    ->label(__('legal.is_enabled'))
                    ->boolean(),
                IconColumn::make('is_required')
                    ->label(__('legal.is_required'))
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label(__('legal.published_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('legal.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('legal.type'))
                    ->options(Legal::getTypes()),
                TernaryFilter::make('is_enabled')
                    ->label(__('legal.is_enabled')),
                TernaryFilter::make('is_required')
                    ->label(__('legal.is_required')),
                Filter::make('published')
                    ->label(__('legal.published'))
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('published_at')),
                Filter::make('draft')
                    ->label(__('legal.draft'))
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereNull('published_at')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('publish')
                    ->label(__('legal.actions.publish'))
                    ->icon('heroicon-o-eye')
                    ->visible(fn (Legal $record): bool => $record->published_at === null)
                    ->requiresConfirmation()
                    ->action(function (Legal $record): void {
                        $record->publish();
                        Notification::make()
                            ->success()
                            ->title(__('legal.notifications.published'))
                            ->send();
                    }),
                Action::make('unpublish')
                    ->label(__('legal.actions.unpublish'))
                    ->icon('heroicon-o-eye-slash')
                    ->visible(fn (Legal $record): bool => $record->published_at !== null)
                    ->requiresConfirmation()
                    ->action(function (Legal $record): void {
                        $record->unpublish();
                        Notification::make()
                            ->warning()
                            ->title(__('legal.notifications.unpublished'))
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    Action::make('bulk_publish')
                        ->label(__('legal.publish_selected'))
                        ->icon('heroicon-o-eye')
                        ->action(function (Collection $records): void {
                            $records->each->publish();
                            Notification::make()
                                ->success()
                                ->title(__('legal.bulk_published_success'))
                                ->send();
                        }),
                    Action::make('bulk_unpublish')
                        ->label(__('legal.unpublish_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->action(function (Collection $records): void {
                            $records->each->unpublish();
                            Notification::make()
                                ->warning()
                                ->title(__('legal.bulk_unpublished_success'))
                                ->send();
                        }),
                    Action::make('bulk_enable')
                        ->label(__('legal.enable_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records): void {
                            $records->each->enable();
                            Notification::make()
                                ->success()
                                ->title(__('legal.bulk_enabled_success'))
                                ->send();
                        }),
                    Action::make('bulk_disable')
                        ->label(__('legal.disable_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->action(function (Collection $records): void {
                            $records->each->disable();
                            Notification::make()
                                ->warning()
                                ->title(__('legal.bulk_disabled_success'))
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            TranslationsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([EnabledScope::class, PublishedScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLegals::route('/'),
            'create' => Pages\CreateLegal::route('/create'),
            'view' => Pages\ViewLegal::route('/{record}'),
            'edit' => Pages\EditLegal::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['key'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->getTranslatedTitle() ?? $record->key;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            __('legal.type') => Legal::getTypes()[$record->type] ?? $record->type,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Legal::query()->withoutGlobalScopes([EnabledScope::class, PublishedScope::class])->count();
    }
}
