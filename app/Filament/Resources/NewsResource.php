<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Models\News;
use App\Services\MultiLanguageTabService;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use SolutionForest\TabLayoutPlugin\Components\Tabs;

final class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static string|\UnitEnum|null $navigationGroup = \App\Enums\NavigationGroup::Content;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.news');
    }

    public static function form(Schema $schema): Schema
    {
        $tabs = MultiLanguageTabService::createSectionedTabs([
            'fields' => [
                'title' => ['type' => 'text', 'label' => __('admin.fields.title'), 'required' => true],
                'slug' => ['type' => 'text', 'label' => 'Slug', 'required' => true],
                'summary' => ['type' => 'textarea', 'label' => __('admin.products.fields.description')],
                'content' => ['type' => 'rich_editor', 'label' => __('admin.products.fields.description')],
                'seo_title' => ['type' => 'text', 'label' => 'SEO Title'],
                'seo_description' => ['type' => 'textarea', 'label' => 'SEO Description'],
            ],
        ]);

        return $schema
            ->sections([
                Section::make(__('admin.navigation.content'))
                    ->schema([
                        Forms\Components\Toggle::make('is_visible')->label(__('admin.products.fields.is_visible'))->default(true),
                        Forms\Components\DateTimePicker::make('published_at')->label(__('admin.fields.published_at'))->native(false),
                        Forms\Components\TextInput::make('author_name')->label(__('admin.fields.author_name')),
                        Tabs::make('translations')->tabs($tabs)->persistTabInQueryString(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.fields.title'))
                    ->getStateUsing(fn(News $record) => $record->trans('title'))
                    ->sortable(searchable: true),
                Tables\Columns\TextColumn::make('published_at')->dateTime()->label(__('admin.fields.published_at')),
                Tables\Columns\IconColumn::make('is_visible')->boolean()->label(__('admin.products.fields.is_visible')),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => NewsResource\Pages\ListNews::route('/'),
            'create' => NewsResource\Pages\CreateNews::route('/create'),
            'edit' => NewsResource\Pages\EditNews::route('/{record}/edit'),
        ];
    }
}
