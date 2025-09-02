<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use App\Services\MultiLanguageTabService;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;

final class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('Campaigns');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Campaign Status and Settings (Non-translatable)
                Forms\Components\Section::make(__('translations.campaign_settings'))
                    ->components([
                        Forms\Components\Select::make('status')
                            ->label(__('translations.status'))
                            ->options([
                                'draft' => __('translations.draft'),
                                'active' => __('translations.active'),
                                'scheduled' => __('translations.scheduled'),
                                'paused' => __('translations.paused'),
                                'completed' => __('translations.completed'),
                                'cancelled' => __('translations.cancelled'),
                            ])
                            ->default('draft')
                            ->required(),
                    ])
                    ->columns(1),

                // Multilanguage Tabs for Campaign Content
                Tabs::make('campaign_translations')
                    ->tabs(
                        MultiLanguageTabService::createSectionedTabs([
                            'campaign_information' => [
                                'name' => [
                                    'type' => 'text',
                                    'label' => __('translations.campaign_name'),
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
                                'description' => [
                                    'type' => 'rich_editor',
                                    'label' => __('translations.campaign_description'),
                                    'toolbar' => [
                                        'bold', 'italic', 'link', 'bulletList', 'orderedList', 
                                        'h2', 'h3', 'blockquote', 'table'
                                    ],
                                ],
                            ],
                        ])
                    )
                    ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
                    ->persistTabInQueryString('campaign_tab')
                    ->contained(false),
                Forms\Components\Section::make(__('Scheduling'))
                    ->components([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label(__('Starts At'))
                            ->required()
                            ->native(false)
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label(__('Ends At'))
                            ->native(false)
                            ->after('starts_at'),
                        Forms\Components\TextInput::make('max_uses')
                            ->label(__('Maximum Uses'))
                            ->numeric()
                            ->minValue(1),
                        Forms\Components\TextInput::make('budget_limit')
                            ->label(__('Budget Limit'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('Discounts'))
                    ->components([
                        Forms\Components\Select::make('discounts')
                            ->label(__('Associated Discounts'))
                            ->relationship('discounts', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ]),
                Forms\Components\Section::make(__('Settings'))
                    ->components([
                        Forms\Components\Toggle::make('is_featured')
                            ->label(__('Featured'))
                            ->default(false),
                        Forms\Components\Toggle::make('send_notifications')
                            ->label(__('Send Notifications'))
                            ->default(true),
                        Forms\Components\Toggle::make('track_conversions')
                            ->label(__('Track Conversions'))
                            ->default(true),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'scheduled' => 'info',
                        'paused' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'draft' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('discounts_count')
                    ->label(__('Discounts'))
                    ->counts('discounts')
                    ->sortable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label(__('Starts At'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label(__('Ends At'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder(__('Unlimited')),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('Featured'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => __('Draft'),
                        'active' => __('Active'),
                        'scheduled' => __('Scheduled'),
                        'paused' => __('Paused'),
                        'completed' => __('Completed'),
                        'cancelled' => __('Cancelled'),
                    ]),
                Tables\Filters\Filter::make('featured')
                    ->query(fn(Builder $query): Builder => $query->where('is_featured', true)),
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
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'view' => Pages\ViewCampaign::route('/{record}'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
