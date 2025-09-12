<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SeoDataResource\Pages;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SeoData;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Actions as Actions;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use BackedEnum;
use UnitEnum;

final class SeoDataResource extends Resource
{
    protected static ?string $model = SeoData::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-globe-alt';


    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.marketing');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.seo_analytics');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('translations.seo_information'))
                    ->components([
                        Forms\Components\Select::make('locale')
                            ->label(__('translations.language'))
                            ->options([
                                'lt' => 'Lietuvių',
                                'en' => 'English',
                            ])
                            ->required()
                            ->default('lt')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('title')
                            ->label(__('translations.seo_title'))
                            ->maxLength(255)
                            ->columnSpan(1),
                        Forms\Components\Textarea::make('description')
                            ->label(__('translations.seo_description'))
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('keywords')
                            ->label(__('translations.meta_keywords'))
                            ->helperText(__('translations.meta_keywords_help'))
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('canonical_url')
                            ->label('Canonical URL')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('meta_tags')
                            ->label('Meta tags')
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('structured_data')
                            ->label('Structured data (JSON-LD)')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('no_index')
                            ->label('noindex')
                            ->inline(false),
                        Forms\Components\Toggle::make('no_follow')
                            ->label('nofollow')
                            ->inline(false),
                    ])
                    ->columns(2),
                Section::make('Relation')
                    ->components([
                        Forms\Components\Select::make('seoable_type')
                            ->label('Tipas')
                            ->options([
                                Product::class => __('admin.models.product'),
                                Category::class => __('admin.models.category'),
                                Brand::class => __('admin.models.brand'),
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set) => $set('seoable_id', null)),
                        Forms\Components\Select::make('seoable_id')
                            ->label(__('translations.name'))
                            ->options(function (callable $get) {
                                $type = $get('seoable_type');
                                if ($type === Product::class) {
                                    return Product::query()->orderBy('name')->pluck('name', 'id');
                                }
                                if ($type === Category::class) {
                                    return Category::query()->orderBy('name')->pluck('name', 'id');
                                }
                                if ($type === Brand::class) {
                                    return Brand::query()->orderBy('name')->pluck('name', 'id');
                                }
                                return [];
                            })
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('seoable_type')
                    ->label(__('translations.type'))
                    ->formatStateUsing(fn(string $state): string => class_basename($state))
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('seoable_id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('locale')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('translations.seo_title'))
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\IconColumn::make('no_index')
                    ->label('noindex')
                    ->boolean(),
                Tables\Columns\IconColumn::make('no_follow')
                    ->label('nofollow')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('locale')
                    ->options(['lt' => 'lt', 'en' => 'en'])
                    ->label(__('translations.language')),
                Tables\Filters\SelectFilter::make('seoable_type')
                    ->options([
                        Product::class => __('admin.models.product'),
                        Category::class => __('admin.models.category'),
                        Brand::class => __('admin.models.brand'),
                    ])
                    ->label(__('translations.type')),
            ])
            ->recordActions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
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
}
