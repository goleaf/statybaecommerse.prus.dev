<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Enums\NavigationGroup;
use App\Filament\Resources\SeoDataResource\Pages;
use App\Models\Category;
use App\Models\Product;
use App\Models\SeoData;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;
/**
 * SeoDataResource
 *
 * Filament v4 resource for SeoData management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class SeoDataResource extends Resource
{
    protected static ?string $model = SeoData::class;    /** @var UnitEnum|string|null */
    protected static string | UnitEnum | null $navigationGroup = "Products";
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'title';
    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('seo_data.title');
    }
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
    public static function getNavigationGroup(): ?string
        return "Content";
     * Handle getPluralModelLabel functionality with proper error handling.
    public static function getPluralModelLabel(): string
        return __('seo_data.plural');
     * Handle getModelLabel functionality with proper error handling.
    public static function getModelLabel(): string
        return __('seo_data.single');
     * Configure the Filament form schema with fields and validation.
     * @param Form $schema
     * @return Form
    public static function form(Schema $schema): Schema
        return $schema->components([
            Section::make(__('seo_data.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('title')
                                ->label(__('seo_data.title'))
                                ->required()
                                ->maxLength(255)
                                ->helperText(__('seo_data.title_help')),
                            TextInput::make('slug')
                                ->label(__('seo_data.slug'))
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash'])
                                ->helperText(__('seo_data.slug_help')),
                        ]),
                    Textarea::make('description')
                        ->label(__('seo_data.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->helperText(__('seo_data.description_help'))
                        ->columnSpanFull(),
                ]),
            Section::make(__('seo_data.meta_information'))
                            TextInput::make('meta_title')
                                ->label(__('seo_data.meta_title'))
                                ->helperText(__('seo_data.meta_title_help')),
                            TextInput::make('meta_description')
                                ->label(__('seo_data.meta_description'))
                                ->maxLength(500)
                                ->helperText(__('seo_data.meta_description_help')),
                            TextInput::make('meta_keywords')
                                ->label(__('seo_data.meta_keywords'))
                                ->helperText(__('seo_data.meta_keywords_help')),
                            TextInput::make('meta_robots')
                                ->label(__('seo_data.meta_robots'))
                                ->maxLength(100)
                                ->default('index, follow')
                                ->helperText(__('seo_data.meta_robots_help')),
            Section::make(__('seo_data.open_graph'))
                            TextInput::make('og_title')
                                ->label(__('seo_data.og_title'))
                                ->helperText(__('seo_data.og_title_help')),
                            TextInput::make('og_description')
                                ->label(__('seo_data.og_description'))
                                ->helperText(__('seo_data.og_description_help')),
                            TextInput::make('og_image')
                                ->label(__('seo_data.og_image'))
                                ->url()
                                ->helperText(__('seo_data.og_image_help')),
                            TextInput::make('og_type')
                                ->label(__('seo_data.og_type'))
                                ->maxLength(50)
                                ->default('website')
                                ->helperText(__('seo_data.og_type_help')),
            Section::make(__('seo_data.twitter_card'))
                            TextInput::make('twitter_title')
                                ->label(__('seo_data.twitter_title'))
                                ->helperText(__('seo_data.twitter_title_help')),
                            TextInput::make('twitter_description')
                                ->label(__('seo_data.twitter_description'))
                                ->helperText(__('seo_data.twitter_description_help')),
                            TextInput::make('twitter_image')
                                ->label(__('seo_data.twitter_image'))
                                ->helperText(__('seo_data.twitter_image_help')),
                            TextInput::make('twitter_card')
                                ->label(__('seo_data.twitter_card'))
                                ->default('summary_large_image')
                                ->helperText(__('seo_data.twitter_card_help')),
            Section::make(__('seo_data.structured_data'))
                    KeyValue::make('structured_data')
                        ->label(__('seo_data.structured_data'))
                        ->keyLabel(__('seo_data.structured_data_key'))
                        ->valueLabel(__('seo_data.structured_data_value'))
                        ->addActionLabel(__('seo_data.add_structured_data_field'))
            Section::make(__('seo_data.settings'))
                            Toggle::make('is_active')
                                ->label(__('seo_data.is_active'))
                                ->default(true),
                            Toggle::make('is_canonical')
                                ->label(__('seo_data.is_canonical'))
                                ->default(false),
                            TextInput::make('canonical_url')
                                ->label(__('seo_data.canonical_url'))
                                ->helperText(__('seo_data.canonical_url_help')),
                            TextInput::make('priority')
                                ->label(__('seo_data.priority'))
                                ->numeric()
                                ->default(0.5)
                                ->minValue(0)
                                ->maxValue(1)
                                ->step(0.1)
                                ->helperText(__('seo_data.priority_help')),
                    Textarea::make('notes')
                        ->label(__('seo_data.notes'))
        ]);
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
    public static function table(Table $table): Table
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('seo_data.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(100),
                TextColumn::make('slug')
                    ->label(__('seo_data.slug'))
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('description')
                    ->label(__('seo_data.description'))
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('meta_title')
                    ->label(__('seo_data.meta_title'))
                TextColumn::make('meta_description')
                    ->label(__('seo_data.meta_description'))
                TextColumn::make('meta_keywords')
                    ->label(__('seo_data.meta_keywords'))
                TextColumn::make('meta_robots')
                    ->label(__('seo_data.meta_robots'))
                    ->color('blue')
                TextColumn::make('og_title')
                    ->label(__('seo_data.og_title'))
                TextColumn::make('og_description')
                    ->label(__('seo_data.og_description'))
                TextColumn::make('og_image')
                    ->label(__('seo_data.og_image'))
                    ->limit(50)
                TextColumn::make('og_type')
                    ->label(__('seo_data.og_type'))
                    ->color('purple')
                TextColumn::make('twitter_title')
                    ->label(__('seo_data.twitter_title'))
                TextColumn::make('twitter_description')
                    ->label(__('seo_data.twitter_description'))
                TextColumn::make('twitter_image')
                    ->label(__('seo_data.twitter_image'))
                TextColumn::make('twitter_card')
                    ->label(__('seo_data.twitter_card'))
                TextColumn::make('canonical_url')
                    ->label(__('seo_data.canonical_url'))
                TextColumn::make('priority')
                    ->label(__('seo_data.priority'))
                    ->numeric()
                    ->alignCenter()
                IconColumn::make('is_active')
                    ->label(__('seo_data.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_canonical')
                    ->label(__('seo_data.is_canonical'))
                TextColumn::make('created_at')
                    ->label(__('seo_data.created_at'))
                    ->dateTime()
                TextColumn::make('updated_at')
                    ->label(__('seo_data.updated_at'))
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->trueLabel(__('seo_data.active_only'))
                    ->falseLabel(__('seo_data.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_canonical')
                    ->trueLabel(__('seo_data.canonical_only'))
                    ->falseLabel(__('seo_data.non_canonical_only'))
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn(SeoData $record): string => $record->is_active ? __('seo_data.deactivate') : __('seo_data.activate'))
                    ->icon(fn(SeoData $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(SeoData $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (SeoData $record): void {
                        $record->update(['is_active' => !$record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? __('seo_data.activated_successfully') : __('seo_data.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('set_canonical')
                    ->label(__('seo_data.set_canonical'))
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->visible(fn(SeoData $record): bool => !$record->is_canonical)
                        // Remove canonical from other SEO data
                        SeoData::where('is_canonical', true)->update(['is_canonical' => false]);
                        // Set this SEO data as canonical
                        $record->update(['is_canonical' => true]);
                            ->title(__('seo_data.set_as_canonical_successfully'))
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('seo_data.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('seo_data.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('seo_data.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                            $records->each->update(['is_active' => false]);
                                ->title(__('seo_data.bulk_deactivated_success'))
            ->defaultSort('created_at', 'desc');
     * Get the relations for this resource.
     * @return array
    public static function getRelations(): array
        return [
            //
        ];
     * Get the pages for this resource.
    public static function getPages(): array
            'index' => Pages\ListSeoData::route('/'),
            'create' => Pages\CreateSeoData::route('/create'),
            'view' => Pages\ViewSeoData::route('/{record}'),
            'edit' => Pages\EditSeoData::route('/{record}/edit'),
}
