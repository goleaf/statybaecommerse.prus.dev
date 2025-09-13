<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\LegalResource\Pages;
use App\Models\Legal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use BackedEnum;
use UnitEnum;

class LegalResource extends Resource
{
    protected static ?string $model = Legal::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static UnitEnum|string|null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'key';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('Legal Document')
                    ->tabs([
                        Tab::make('Basic Information')
                            ->schema([
                                Section::make('Document Details')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('key')
                                                    ->label(__('admin.legal.key'))
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->maxLength(255)
                                                    ->helperText(__('admin.legal.key_help')),

                                                Select::make('type')
                                                    ->label(__('admin.legal.type'))
                                                    ->options(Legal::getTypes())
                                                    ->required()
                                                    ->default('legal_document')
                                                    ->searchable(),
                                            ]),

                                        Grid::make(3)
                                            ->schema([
                                                Toggle::make('is_enabled')
                                                    ->label(__('admin.legal.is_enabled'))
                                                    ->default(true)
                                                    ->helperText(__('admin.legal.is_enabled_help')),

                                                Toggle::make('is_required')
                                                    ->label(__('admin.legal.is_required'))
                                                    ->default(false)
                                                    ->helperText(__('admin.legal.is_required_help')),

                                                TextInput::make('sort_order')
                                                    ->label(__('admin.legal.sort_order'))
                                                    ->numeric()
                                                    ->default(0)
                                                    ->helperText(__('admin.legal.sort_order_help')),
                                            ]),

                                        DateTimePicker::make('published_at')
                                            ->label(__('admin.legal.published_at'))
                                            ->helperText(__('admin.legal.published_at_help')),
                                    ])
                                    ->columns(1),
                            ]),

                        Tab::make('Translations')
                            ->schema([
                                Section::make('Lithuanian (LT)')
                                    ->schema([
                                        TextInput::make('translations.lt.title')
                                            ->label(__('admin.legal.title'))
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                                if ($state) {
                                                    $set('translations.lt.slug', Str::slug($state) . '-lt');
                                                }
                                            }),

                                        TextInput::make('translations.lt.slug')
                                            ->label(__('admin.legal.slug'))
                                            ->required()
                                            ->maxLength(255)
                                            ->unique('legal_translations', 'slug', ignoreRecord: true),

                                        RichEditor::make('translations.lt.content')
                                            ->label(__('admin.legal.content'))
                                            ->required()
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'underline',
                                                'strike',
                                                'link',
                                                'bulletList',
                                                'orderedList',
                                                'h2',
                                                'h3',
                                                'blockquote',
                                                'codeBlock',
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('translations.lt.seo_title')
                                                    ->label(__('admin.legal.seo_title'))
                                                    ->maxLength(255)
                                                    ->helperText(__('admin.legal.seo_title_help')),

                                                Textarea::make('translations.lt.seo_description')
                                                    ->label(__('admin.legal.seo_description'))
                                                    ->maxLength(500)
                                                    ->rows(3)
                                                    ->helperText(__('admin.legal.seo_description_help')),
                                            ]),
                                    ])
                                    ->columns(1),

                                Section::make('English (EN)')
                                    ->schema([
                                        TextInput::make('translations.en.title')
                                            ->label(__('admin.legal.title'))
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                                if ($state) {
                                                    $set('translations.en.slug', Str::slug($state) . '-en');
                                                }
                                            }),

                                        TextInput::make('translations.en.slug')
                                            ->label(__('admin.legal.slug'))
                                            ->required()
                                            ->maxLength(255)
                                            ->unique('legal_translations', 'slug', ignoreRecord: true),

                                        RichEditor::make('translations.en.content')
                                            ->label(__('admin.legal.content'))
                                            ->required()
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'underline',
                                                'strike',
                                                'link',
                                                'bulletList',
                                                'orderedList',
                                                'h2',
                                                'h3',
                                                'blockquote',
                                                'codeBlock',
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('translations.en.seo_title')
                                                    ->label(__('admin.legal.seo_title'))
                                                    ->maxLength(255)
                                                    ->helperText(__('admin.legal.seo_title_help')),

                                                Textarea::make('translations.en.seo_description')
                                                    ->label(__('admin.legal.seo_description'))
                                                    ->maxLength(500)
                                                    ->rows(3)
                                                    ->helperText(__('admin.legal.seo_description_help')),
                                            ]),
                                    ])
                                    ->columns(1),
                            ]),

                        Tab::make('Metadata')
                            ->schema([
                                Section::make('Additional Information')
                                    ->schema([
                                        KeyValue::make('meta_data')
                                            ->label(__('admin.legal.meta_data'))
                                            ->keyLabel(__('admin.legal.meta_key'))
                                            ->valueLabel(__('admin.legal.meta_value'))
                                            ->helperText(__('admin.legal.meta_data_help')),
                                    ])
                                    ->columns(1),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(__('admin.legal.key'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('type')
                    ->label(__('admin.legal.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'privacy_policy' => 'danger',
                        'terms_of_use' => 'warning',
                        'refund_policy' => 'info',
                        'shipping_policy' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Legal::getTypes()[$state] ?? $state),

                TextColumn::make('translations.title')
                    ->label(__('admin.legal.title'))
                    ->getStateUsing(function (Legal $record): string {
                        $translation = $record->translations()
                            ->where('locale', app()->getLocale())
                            ->first();
                        
                        return $translation?->title ?? $record->key;
                    })
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_enabled')
                    ->label(__('admin.legal.is_enabled'))
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_required')
                    ->label(__('admin.legal.is_required'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('admin.legal.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        'disabled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('sort_order')
                    ->label(__('admin.legal.sort_order'))
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('published_at')
                    ->label(__('admin.legal.published_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('admin.legal.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('admin.legal.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('admin.legal.type'))
                    ->options(Legal::getTypes())
                    ->multiple(),

                TernaryFilter::make('is_enabled')
                    ->label(__('admin.legal.is_enabled')),

                TernaryFilter::make('is_required')
                    ->label(__('admin.legal.is_required')),

                SelectFilter::make('status')
                    ->label(__('admin.legal.status'))
                    ->options([
                        'published' => __('admin.legal.status_published'),
                        'draft' => __('admin.legal.status_draft'),
                        'disabled' => __('admin.legal.status_disabled'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value']) {
                            'published' => $query->where('is_enabled', true)->whereNotNull('published_at'),
                            'draft' => $query->whereNull('published_at'),
                            'disabled' => $query->where('is_enabled', false),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Action::make('view')
                    ->label(__('admin.legal.view'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (Legal $record): string => route('legal.show', $record->key))
                    ->openUrlInNewTab(),

                Action::make('publish')
                    ->label(__('admin.legal.publish'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Legal $record): bool => !$record->is_published)
                    ->action(function (Legal $record): void {
                        $record->publish();
                        
                        Notification::make()
                            ->title(__('admin.legal.published_successfully'))
                            ->success()
                            ->send();
                    }),

                Action::make('unpublish')
                    ->label(__('admin.legal.unpublish'))
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn (Legal $record): bool => $record->is_published)
                    ->action(function (Legal $record): void {
                        $record->unpublish();
                        
                        Notification::make()
                            ->title(__('admin.legal.unpublished_successfully'))
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label(__('admin.legal.publish_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->publish();
                            
                            Notification::make()
                                ->title(__('admin.legal.published_selected_successfully'))
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('unpublish')
                        ->label(__('admin.legal.unpublish_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->unpublish();
                            
                            Notification::make()
                                ->title(__('admin.legal.unpublished_selected_successfully'))
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('enable')
                        ->label(__('admin.legal.enable_selected'))
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->enable();
                            
                            Notification::make()
                                ->title(__('admin.legal.enabled_selected_successfully'))
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('disable')
                        ->label(__('admin.legal.disable_selected'))
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $records->each->disable();
                            
                            Notification::make()
                                ->title(__('admin.legal.disabled_selected_successfully'))
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkDeleteAction::make(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
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
            'index' => Pages\ListLegals::route('/'),
            'create' => Pages\CreateLegal::route('/create'),
            'edit' => Pages\EditLegal::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('is_enabled', false)->count() > 0 ? 'warning' : 'primary';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['translations']);
    }
}
