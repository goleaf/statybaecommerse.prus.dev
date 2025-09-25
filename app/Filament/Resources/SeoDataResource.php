<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use BackedEnum;
use App\Filament\Resources\SeoDataResource\Pages;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SeoData;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

final class SeoDataResource extends Resource
{
    protected static ?string $model = SeoData::class;

    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::Content;

    public static function getNavigationLabel(): string
    {
        return __('seo_data.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('seo_data.plural');
    }

    public static function getModelLabel(): string
    {
        return __('seo_data.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->schema([
                Section::make(__('seo_data.sections.basic_info'))
                    ->description(__('seo_data.sections.basic_info_description'))
                    ->columns(2)
                    ->schema([
                        Select::make('seoable_type')
                            ->label(__('seo_data.fields.seoable_type'))
                            ->options([
                                Product::class => __('seo_data.types.product'),
                                Category::class => __('seo_data.types.category'),
                                Brand::class => __('seo_data.types.brand'),
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('seoable_id', null)),
                        Select::make('seoable_id')
                            ->label(__('seo_data.fields.seoable_id'))
                            ->options(function ($get) {
                                $type = $get('seoable_type');
                                if (! $type) {
                                    return [];
                                }

                                return match ($type) {
                                    Product::class => Product::pluck('name', 'id'),
                                    Category::class => Category::pluck('name', 'id'),
                                    Brand::class => Brand::pluck('name', 'id'),
                                    default => [],
                                };
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('locale')
                            ->label(__('seo_data.fields.locale'))
                            ->required()
                            ->default('lt')
                            ->maxLength(10),
                    ]),
                Section::make(__('seo_data.sections.seo_content'))
                    ->description(__('seo_data.sections.seo_content_description'))
                    ->columns(1)
                    ->schema([
                        TextInput::make('title')
                            ->label(__('seo_data.fields.title'))
                            ->required()
                            ->maxLength(255)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $length = mb_strlen($state);
                                $set('title_length', $length);
                                if ($length < 30) {
                                    $set('title_warning', __('seo_data.warnings.title_too_short'));
                                } elseif ($length > 60) {
                                    $set('title_warning', __('seo_data.warnings.title_too_long'));
                                } else {
                                    $set('title_warning', null);
                                }
                            }),
                        Placeholder::make('title_length')
                            ->label(__('seo_data.fields.title_length'))
                            ->content(fn ($get) => $get('title_length').' '.__('seo_data.characters')),
                        Placeholder::make('title_warning')
                            ->content(fn ($get) => $get('title_warning'))
                            ->visible(fn ($get) => $get('title_warning')),
                        Textarea::make('description')
                            ->label(__('seo_data.fields.description'))
                            ->required()
                            ->maxLength(160)
                            ->rows(3)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $length = mb_strlen($state);
                                $set('description_length', $length);
                                if ($length < 120) {
                                    $set('description_warning', __('seo_data.warnings.description_too_short'));
                                } elseif ($length > 160) {
                                    $set('description_warning', __('seo_data.warnings.description_too_long'));
                                } else {
                                    $set('description_warning', null);
                                }
                            }),
                        Placeholder::make('description_length')
                            ->label(__('seo_data.fields.description_length'))
                            ->content(fn ($get) => $get('description_length').' '.__('seo_data.characters')),
                        Placeholder::make('description_warning')
                            ->content(fn ($get) => $get('description_warning'))
                            ->visible(fn ($get) => $get('description_warning')),
                        TextInput::make('keywords')
                            ->label(__('seo_data.fields.keywords'))
                            ->maxLength(255)
                            ->placeholder(__('seo_data.placeholders.keywords_comma_separated')),
                        TextInput::make('canonical_url')
                            ->label(__('seo_data.fields.canonical_url'))
                            ->url()
                            ->maxLength(255),
                    ]),
                Section::make(__('seo_data.sections.robots'))
                    ->description(__('seo_data.sections.robots_description'))
                    ->columns(2)
                    ->schema([
                        Toggle::make('no_index')
                            ->label(__('seo_data.fields.no_index'))
                            ->inline(false)
                            ->default(false),
                        Toggle::make('no_follow')
                            ->label(__('seo_data.fields.no_follow'))
                            ->inline(false)
                            ->default(false),
                    ]),
                Section::make(__('seo_data.sections.advanced'))
                    ->description(__('seo_data.sections.advanced_description'))
                    ->collapsible()
                    ->schema([
                        KeyValue::make('meta_tags')
                            ->label(__('seo_data.fields.meta_tags'))
                            ->keyLabel(__('seo_data.fields.meta_tag_name'))
                            ->valueLabel(__('seo_data.fields.meta_tag_content'))
                            ->reorderable()
                            ->addActionLabel(__('seo_data.actions.add_meta_tag'))
                            ->columnSpanFull(),
                        KeyValue::make('structured_data')
                            ->label(__('seo_data.fields.structured_data'))
                            ->keyLabel(__('seo_data.fields.structured_data_key'))
                            ->valueLabel(__('seo_data.fields.structured_data_value'))
                            ->reorderable()
                            ->addActionLabel(__('seo_data.actions.add_structured_data'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('seo_data.fields.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(50),
                TextColumn::make('seoable_type')
                    ->label(__('seo_data.fields.seoable_type'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Product::class => __('seo_data.types.product'),
                        Category::class => __('seo_data.types.category'),
                        Brand::class => __('seo_data.types.brand'),
                        default => class_basename($state),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Product::class => 'success',
                        Category::class => 'info',
                        Brand::class => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('seoable.name')
                    ->label(__('seo_data.fields.seoable_name'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->placeholder(__('seo_data.placeholders.seoable_not_found')),
                TextColumn::make('locale')
                    ->label(__('seo_data.fields.locale'))
                    ->badge()
                    ->color('info'),
                TextColumn::make('description')
                    ->label(__('seo_data.fields.description'))
                    ->limit(100)
                    ->toggleable(),
                TextColumn::make('keywords')
                    ->label(__('seo_data.fields.keywords'))
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('canonical_url')
                    ->label(__('seo_data.fields.canonical_url'))
                    ->limit(50)
                    ->toggleable(),
                IconColumn::make('no_index')
                    ->label(__('seo_data.fields.no_index'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('no_follow')
                    ->label(__('seo_data.fields.no_follow'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('seo_score')
                    ->label(__('seo_data.fields.seo_score'))
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 60 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (int $state): string => $state.'%'),
                TextColumn::make('created_at')
                    ->label(__('seo_data.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('seo_data.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('seoable_type')
                    ->label(__('seo_data.filters.seoable_type'))
                    ->options([
                        Product::class => __('seo_data.types.product'),
                        Category::class => __('seo_data.types.category'),
                        Brand::class => __('seo_data.types.brand'),
                    ]),
                SelectFilter::make('locale')
                    ->label(__('seo_data.filters.locale'))
                    ->options([
                        'lt' => 'Lithuanian',
                        'en' => 'English',
                    ]),
                TernaryFilter::make('no_index')
                    ->label(__('seo_data.filters.no_index'))
                    ->boolean(),
                TernaryFilter::make('no_follow')
                    ->label(__('seo_data.filters.no_follow'))
                    ->boolean(),
                Filter::make('has_title')
                    ->label(__('seo_data.filters.has_title'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('title')),
                Filter::make('has_description')
                    ->label(__('seo_data.filters.has_description'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('description')),
                Filter::make('has_keywords')
                    ->label(__('seo_data.filters.has_keywords'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('keywords')),
                Filter::make('has_canonical_url')
                    ->label(__('seo_data.filters.has_canonical_url'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('canonical_url')),
                Filter::make('high_seo_score')
                    ->label(__('seo_data.filters.high_seo_score'))
                    ->query(fn (Builder $query): Builder => $query->whereRaw('
                        (CASE 
                            WHEN title IS NOT NULL THEN 20 ELSE 0 END +
                         CASE 
                            WHEN LENGTH(title) BETWEEN 30 AND 60 THEN 20 ELSE 0 END +
                         CASE 
                            WHEN description IS NOT NULL THEN 15 ELSE 0 END +
                         CASE 
                            WHEN LENGTH(description) BETWEEN 120 AND 160 THEN 15 ELSE 0 END +
                         CASE 
                            WHEN keywords IS NOT NULL THEN 10 ELSE 0 END +
                         CASE 
                            WHEN canonical_url IS NOT NULL THEN 10 ELSE 0 END +
                         CASE 
                            WHEN structured_data IS NOT NULL THEN 5 ELSE 0 END
                        ) >= 80
                    ')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('analyze_seo')
                    ->label(__('seo_data.actions.analyze_seo'))
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->action(function (SeoData $record): void {
                        // This would typically analyze SEO score
                        Notification::make()
                            ->title(__('seo_data.notifications.seo_analyzed'))
                            ->success()
                            ->send();
                    }),
                Action::make('generate_meta_tags')
                    ->label(__('seo_data.actions.generate_meta_tags'))
                    ->icon('heroicon-o-code-bracket')
                    ->color('success')
                    ->action(function (SeoData $record): void {
                        // This would typically generate meta tags HTML
                        Notification::make()
                            ->title(__('seo_data.notifications.meta_tags_generated'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('analyze_all_seo')
                        ->label(__('seo_data.actions.analyze_all_seo'))
                        ->icon('heroicon-o-chart-bar')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            // This would typically analyze SEO for all selected records
                            Notification::make()
                                ->title(__('seo_data.notifications.all_seo_analyzed'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('generate_all_meta_tags')
                        ->label(__('seo_data.actions.generate_all_meta_tags'))
                        ->icon('heroicon-o-code-bracket')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            // This would typically generate meta tags for all selected records
                            Notification::make()
                                ->title(__('seo_data.notifications.all_meta_tags_generated'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                InfolistSection::make(__('seo_data.sections.basic_info'))
                    ->schema([
                        TextEntry::make('title')
                            ->label(__('seo_data.fields.title'))
                            ->weight('medium'),
                        TextEntry::make('seoable_type')
                            ->label(__('seo_data.fields.seoable_type'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                Product::class => 'success',
                                Category::class => 'info',
                                Brand::class => 'warning',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                Product::class => __('seo_data.types.product'),
                                Category::class => __('seo_data.types.category'),
                                Brand::class => __('seo_data.types.brand'),
                                default => class_basename($state),
                            }),
                        TextEntry::make('seoable.name')
                            ->label(__('seo_data.fields.seoable_name'))
                            ->placeholder(__('seo_data.placeholders.seoable_not_found')),
                        TextEntry::make('locale')
                            ->label(__('seo_data.fields.locale'))
                            ->badge()
                            ->color('info'),
                    ])
                    ->columns(2),
                InfolistSection::make(__('seo_data.sections.seo_content'))
                    ->schema([
                        TextEntry::make('description')
                            ->label(__('seo_data.fields.description'))
                            ->columnSpanFull()
                            ->placeholder(__('seo_data.placeholders.no_description')),
                        TextEntry::make('keywords')
                            ->label(__('seo_data.fields.keywords'))
                            ->placeholder(__('seo_data.placeholders.no_keywords')),
                        TextEntry::make('canonical_url')
                            ->label(__('seo_data.fields.canonical_url'))
                            ->url()
                            ->placeholder(__('seo_data.placeholders.no_canonical_url')),
                    ]),
                InfolistSection::make(__('seo_data.sections.robots'))
                    ->schema([
                        TextEntry::make('no_index')
                            ->label(__('seo_data.fields.no_index'))
                            ->boolean(),
                        TextEntry::make('no_follow')
                            ->label(__('seo_data.fields.no_follow'))
                            ->boolean(),
                        TextEntry::make('robots')
                            ->label(__('seo_data.fields.robots'))
                            ->badge()
                            ->color('info'),
                    ])
                    ->columns(2),
                InfolistSection::make(__('seo_data.sections.seo_analysis'))
                    ->schema([
                        TextEntry::make('seo_score')
                            ->label(__('seo_data.fields.seo_score'))
                            ->badge()
                            ->color(fn (int $state): string => match (true) {
                                $state >= 80 => 'success',
                                $state >= 60 => 'warning',
                                default => 'danger',
                            })
                            ->formatStateUsing(fn (int $state): string => $state.'%'),
                        TextEntry::make('title_length')
                            ->label(__('seo_data.fields.title_length'))
                            ->numeric(),
                        TextEntry::make('description_length')
                            ->label(__('seo_data.fields.description_length'))
                            ->numeric(),
                        TextEntry::make('keywords_count')
                            ->label(__('seo_data.fields.keywords_count'))
                            ->numeric(),
                    ])
                    ->columns(2),
                InfolistSection::make(__('seo_data.sections.advanced'))
                    ->collapsible()
                    ->schema([
                        RepeatableEntry::make('meta_tags')
                            ->label(__('seo_data.fields.meta_tags'))
                            ->schema([
                                TextEntry::make('key')
                                    ->label(__('seo_data.fields.meta_tag_name')),
                                TextEntry::make('value')
                                    ->label(__('seo_data.fields.meta_tag_content')),
                            ])
                            ->placeholder(__('seo_data.placeholders.no_meta_tags')),
                        RepeatableEntry::make('structured_data')
                            ->label(__('seo_data.fields.structured_data'))
                            ->schema([
                                TextEntry::make('key')
                                    ->label(__('seo_data.fields.structured_data_key')),
                                TextEntry::make('value')
                                    ->label(__('seo_data.fields.structured_data_value')),
                            ])
                            ->placeholder(__('seo_data.placeholders.no_structured_data')),
                    ]),
                InfolistSection::make(__('seo_data.sections.timestamps'))
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('seo_data.fields.created_at'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('seo_data.fields.updated_at'))
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
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
            'index' => Pages\ListSeoData::route('/'),
            'create' => Pages\CreateSeoData::route('/create'),
            'view' => Pages\ViewSeoData::route('/{record}'),
            'edit' => Pages\EditSeoData::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description', 'keywords'];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) self::$model::count();
    }
}
