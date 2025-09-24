<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\NewsTagResource\Pages;
use App\Models\NewsTag;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Grid as FormGrid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkAction as TableBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

final class NewsTagResource extends Resource
{
    protected static ?string $model = NewsTag::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-tag';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return NavigationGroup::News;
    }

    protected static ?int $navigationSort = 4;

    public static function getPluralModelLabel(): string
    {
        return __('admin.news_tags.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.news_tags.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            FormSection::make(__('admin.news_tags.form.sections.basic_information'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('admin.news_tags.form.fields.name'))
                        ->required()
                        ->maxLength(255)
                        ->live()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Str::slug($state))),
                    TextInput::make('slug')
                        ->label(__('admin.news_tags.form.fields.slug'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Textarea::make('description')
                        ->label(__('admin.news_tags.form.fields.description'))
                        ->rows(3)
                        ->maxLength(1000),
                    FormGrid::make(3)
                        ->schema([
                            Toggle::make('is_visible')
                                ->label(__('admin.news_tags.form.fields.is_visible'))
                                ->default(true)
                                ->columnSpan(1),
                            TextInput::make('sort_order')
                                ->label(__('admin.news_tags.form.fields.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->columnSpan(1),
                            ColorPicker::make('color')
                                ->label(__('admin.news_tags.form.fields.color'))
                                ->default('#3B82F6')
                                ->columnSpan(1),
                        ]),
                ])
                ->columns(1),
            FormSection::make(__('admin.news_tags.form.sections.translations'))
                ->schema([
                    Repeater::make('translations')
                        ->label(__('admin.news_tags.form.fields.translations'))
                        ->schema([
                            Select::make('locale')
                                ->label(__('admin.news_tags.form.fields.locale'))
                                ->options([
                                    'lt' => 'Lietuvių',
                                    'en' => 'English',
                                ])
                                ->required(),
                            TextInput::make('name')
                                ->label(__('admin.news_tags.form.fields.name'))
                                ->required(),
                            TextInput::make('slug')
                                ->label(__('admin.news_tags.form.fields.slug'))
                                ->required(),
                            Textarea::make('description')
                                ->label(__('admin.news_tags.form.fields.description'))
                                ->rows(2),
                        ])
                        ->columns(2)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['locale'] ?? null),
                ])
                ->columns(1)
                ->collapsible(),
            FormSection::make(__('admin.news_tags.form.sections.metadata'))
                ->schema([
                    Placeholder::make('created_at')
                        ->label(__('admin.news_tags.form.fields.created_at'))
                        ->content(fn ($record) => $record?->created_at?->format('Y-m-d H:i:s') ?? '-'),
                    Placeholder::make('updated_at')
                        ->label(__('admin.news_tags.form.fields.updated_at'))
                        ->content(fn ($record) => $record?->updated_at?->format('Y-m-d H:i:s') ?? '-'),
                    Placeholder::make('news_count')
                        ->label(__('admin.news_tags.form.fields.news_count'))
                        ->content(fn ($record) => $record?->news()->count() ?? 0),
                ])
                ->columns(3)
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.news_tags.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('slug')
                    ->label(__('admin.news_tags.table.slug'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('description')
                    ->label(__('admin.news_tags.table.description'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('color')
                    ->label(__('admin.news_tags.table.color'))
                    ->colors([
                        'primary' => fn ($state): bool => $state === '#3B82F6',
                        'success' => fn ($state): bool => $state === '#10B981',
                        'warning' => fn ($state): bool => $state === '#F59E0B',
                        'danger' => fn ($state): bool => $state === '#EF4444',
                    ])
                    ->formatStateUsing(fn ($state): string => $state ?? '#3B82F6')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_visible')
                    ->label(__('admin.news_tags.table.is_visible'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('sort_order')
                    ->label(__('admin.news_tags.table.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('news_count')
                    ->label(__('admin.news_tags.table.news_count'))
                    ->counts('news')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('created_at')
                    ->label(__('admin.news_tags.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('is_visible')
                    ->label(__('admin.news_tags.filters.active'))
                    ->query(fn (Builder $query): Builder => $query->where('is_visible', true)),
                Filter::make('inactive')
                    ->label(__('admin.news_tags.filters.inactive'))
                    ->query(fn (Builder $query): Builder => $query->where('is_visible', false)),
                Filter::make('with_news')
                    ->label(__('admin.news_tags.filters.with_news'))
                    ->query(fn (Builder $query): Builder => $query->has('news')),
                Filter::make('without_news')
                    ->label(__('admin.news_tags.filters.without_news'))
                    ->query(fn (Builder $query): Builder => $query->doesntHave('news')),
                SelectFilter::make('color')
                    ->label(__('admin.news_tags.filters.color'))
                    ->options([
                        '#3B82F6' => 'Primary',
                        '#10B981' => 'Success',
                        '#F59E0B' => 'Warning',
                        '#EF4444' => 'Danger',
                    ]),
                Filter::make('recent')
                    ->label(__('admin.news_tags.filters.recent'))
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7))),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                TableBulkAction::make('activate')
                    ->label(__('admin.news_tags.actions.activate'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (NewsTag $record): void {
                        $record->update(['is_visible' => true]);
                        Notification::make()
                            ->title(__('admin.news_tags.activated_successfully'))
                            ->success()
                            ->send();
                    }),
                TableBulkAction::make('deactivate')
                    ->label(__('admin.news_tags.actions.deactivate'))
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->action(function (NewsTag $record): void {
                        $record->update(['is_visible' => false]);
                        Notification::make()
                            ->title(__('admin.news_tags.deactivated_successfully'))
                            ->success()
                            ->send();
                    }),
                TableBulkAction::make('duplicate')
                    ->label(__('admin.news_tags.actions.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (NewsTag $record): void {
                        $newTag = $record->replicate();
                        $newTag->name = $record->name.' (Copy)';
                        $newTag->slug = $record->slug.'-copy';
                        $newTag->save();

                        Notification::make()
                            ->title(__('admin.news_tags.duplicated_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableBulkAction::make('bulk_activate')
                        ->label(__('admin.news_tags.actions.bulk_activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(function (NewsTag $record): void {
                                $record->update(['is_visible' => true]);
                            });
                            Notification::make()
                                ->title(__('admin.news_tags.bulk_activated_successfully'))
                                ->success()
                                ->send();
                        }),
                    TableBulkAction::make('bulk_deactivate')
                        ->label(__('admin.news_tags.actions.bulk_deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each(function (NewsTag $record): void {
                                $record->update(['is_visible' => false]);
                            });
                            Notification::make()
                                ->title(__('admin.news_tags.bulk_deactivated_successfully'))
                                ->success()
                                ->send();
                        }),
                    TableBulkAction::make('bulk_duplicate')
                        ->label(__('admin.news_tags.actions.bulk_duplicate'))
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $records->each(function (NewsTag $record): void {
                                $newTag = $record->replicate();
                                $newTag->name = $record->name.' (Copy)';
                                $newTag->slug = $record->slug.'-copy';
                                $newTag->save();
                            });
                            Notification::make()
                                ->title(__('admin.news_tags.bulk_duplicated_successfully'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
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
            'index' => Pages\ListNewsTags::route('/'),
            'create' => Pages\CreateNewsTag::route('/create'),
            'view' => Pages\ViewNewsTag::route('/{record}'),
            'edit' => Pages\EditNewsTag::route('/{record}/edit'),
        ];
    }
}
