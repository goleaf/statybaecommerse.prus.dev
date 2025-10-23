<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Forms\Components\Flatpickr;
use App\Filament\Resources\LegalResource\Pages;
use App\Filament\Resources\LegalResource\RelationManagers\TranslationsRelationManager;
use App\Models\Legal;
use App\Support\Filament\Components\Flatpickr;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use UnitEnum;
use App\Support\Filament\Components\Flatpickr;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
final class LegalResource extends Resource
{
    protected static ?string $model = Legal::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'key';

    public static function getNavigationIcon(): BackedEnum|\UnitEnum|Htmlable|string|null
    {
        return 'heroicon-o-scale';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return __('navigation.groups.documents');
    }

    public static function getNavigationLabel(): string
    {
        return __('legal.plural');
    }

    public static function getPluralModelLabel(): string
    {
        return __('legal.plural');
    }

    public static function getModelLabel(): string
    {
        return __('legal.single');
    }

    public static function form(Form $form): Form
    {
        return $schema
            ->schema([
                Forms\Components\Section::make(__('legal.basic_information'))
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('key')
                                    ->label(__('legal.key'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Legal::class, 'key', ignoreRecord: true)
                                    ->helperText(__('legal.key_help')),
                                Forms\Components\Select::make('type')
                                    ->label(__('legal.type'))
                                    ->options(Legal::getTypes())
                                    ->searchable()
                                    ->required(),
                                Forms\Components\Toggle::make('is_enabled')
                                    ->label(__('legal.is_enabled'))
                                    ->default(true),
                                Forms\Components\Toggle::make('is_required')
                                    ->label(__('legal.is_required'))
                                    ->default(false),
                                Forms\Components\TextInput::make('sort_order')
                                    ->label(__('legal.sort_order'))
                                    ->numeric()
                                    ->default(0),
                                Flatpickr::makeDateTime('published_at')
                                    ->label(__('legal.published_at'))
                                    ->seconds(false)
                                    ->timezone(config('app.timezone')),
                            ]),
                        Forms\Components\KeyValue::make('meta_data')
                            ->label(__('legal.meta_data'))
                            ->helperText(__('legal.meta_data_help'))
                            ->columnSpanFull()
                            ->keyLabel('key')
                            ->valueLabel('value')
                            ->reorderable(),
                    ])
                    ->columns(1),
                Forms\Components\Section::make(__('legal.translations'))
                    ->schema([
                        Forms\Components\Repeater::make('translations')
                            ->label(__('legal.translations'))
                            ->relationship('translations')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('locale')
                                            ->label(__('legal.locale'))
                                            ->options([
                                                'en' => 'English',
                                                'lt' => 'Lietuvių',
                                                'ru' => 'Русский',
                                                'de' => 'Deutsch',
                                            ])
                                            ->required()
                                            ->searchable(),
                                        Forms\Components\TextInput::make('slug')
                                            ->label(__('legal.slug'))
                                            ->helperText(__('legal.slug_help'))
                                            ->required()
                                            ->maxLength(255)
                                            ->unique('legal_translations', 'slug', ignoreRecord: true),
                                    ]),
                                Forms\Components\TextInput::make('title')
                                    ->label(__('legal.title'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                        $currentSlug = $get('slug');

                                        if (! filled($currentSlug)) {
                                            $set('slug', $state ? Str::slug($state) : null);
                                        }
                                    }),
                                Forms\Components\RichEditor::make('content')
                                    ->label(__('legal.content'))
                                    ->required()
                                    ->columnSpanFull(),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('seo_title')
                                            ->label(__('legal.seo_title'))
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('seo_description')
                                            ->label(__('legal.seo_description'))
                                            ->rows(3)
                                            ->maxLength(500),
                                    ]),
                            ])
                            ->minItems(0)
                            ->columns(1)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['locale'] ?? null)
                            ->defaultItems(0),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label(__('legal.key'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('legal.type'))
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => Legal::getTypes()[$state] ?? $state)
                    ->badge(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('legal.is_enabled'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_required')
                    ->label(__('legal.is_required'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('legal.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft'     => 'warning',
                        'disabled'  => 'danger',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('legal.published_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('legal.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('published_at')),
                Filter::make('draft')
                    ->label(__('legal.draft'))
                    ->query(fn (Builder $query): Builder => $query->whereNull('published_at')),
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
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            TranslationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLegals::route('/'),
            'create' => Pages\CreateLegal::route('/create'),
            'view'   => Pages\ViewLegal::route('/{record}'),
            'edit'   => Pages\EditLegal::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('translations');
    }
}