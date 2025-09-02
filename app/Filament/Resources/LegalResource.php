<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\LegalResource\Pages;
use App\Models\Legal;
use App\Services\MultiLanguageTabService;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;
use BackedEnum;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;

final class LegalResource extends Resource
{
    protected static ?string $model = Legal::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('translations.legal_pages');
    }

    public static function getModelLabel(): string
    {
        return __('translations.legal_page');
    }

    public static function getPluralModelLabel(): string
    {
        return __('translations.legal_pages');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Main Legal Information (Non-translatable)
                Forms\Components\Section::make(__('translations.legal_information'))
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->label(__('translations.key'))
                            ->required()
                            ->maxLength(255)
                            ->unique(Legal::class, 'key', ignoreRecord: true)
                            ->helperText(__('translations.legal_key_help')),
                        Forms\Components\Toggle::make('is_enabled')
                            ->label(__('translations.enabled'))
                            ->default(true),
                    ])
                    ->columns(2),

                // Multilanguage Tabs for Translatable Content
                Tabs::make('legal_translations')
                    ->tabs(
                        MultiLanguageTabService::createSectionedTabs([
                            'content_information' => [
                                'title' => [
                                    'type' => 'text',
                                    'label' => __('translations.title'),
                                    'required' => true,
                                    'maxLength' => 255,
                                ],
                                'slug' => [
                                    'type' => 'text',
                                    'label' => __('translations.slug'),
                                    'required' => true,
                                    'maxLength' => 255,
                                    'placeholder' => __('translations.slug_auto_generated'),
                                ],
                                'content' => [
                                    'type' => 'rich_editor',
                                    'label' => __('translations.content'),
                                    'toolbar' => [
                                        'bold', 'italic', 'link', 'bulletList', 'orderedList', 
                                        'h2', 'h3', 'blockquote', 'codeBlock', 'table'
                                    ],
                                ],
                            ],
                        ])
                    )
                    ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
                    ->persistTabInQueryString('legal_tab')
                    ->contained(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label(__('translations.key'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('translations.enabled'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('translations_count')
                    ->label(__('translations.translations'))
                    ->counts('translations')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('translations.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label(__('translations.enabled'))
                    ->placeholder(__('translations.all'))
                    ->trueLabel(__('translations.enabled'))
                    ->falseLabel(__('translations.disabled')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListLegals::route('/'),
            'create' => Pages\CreateLegal::route('/create'),
            'view' => Pages\ViewLegal::route('/{record}'),
            'edit' => Pages\EditLegal::route('/{record}/edit'),
        ];
    }
}
