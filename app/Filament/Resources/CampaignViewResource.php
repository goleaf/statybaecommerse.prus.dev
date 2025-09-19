<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CampaignViewResource\Pages;
use App\Models\Campaign;
use App\Models\CampaignView;
use App\Models\User;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

final class CampaignViewResource extends Resource
{
    protected static ?string $model = CampaignView::class;

    // protected static $navigationGroup = NavigationGroup::Marketing;

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'ip_address';

    public static function getModelLabel(): string
    {
        return __('campaign_views.single');
    }

    public static function getPluralModelLabel(): string
    {
        return __('campaign_views.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('campaign_views.navigation');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make(__('campaign_views.tabs'))
                    ->tabs([
                        Tab::make(__('campaign_views.basic_information'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Select::make('campaign_id')
                                    ->label(__('campaign_views.campaign'))
                                    ->relationship('campaign', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('user_id')
                                    ->label(__('campaign_views.user'))
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('ip_address')
                                    ->label(__('campaign_views.ip_address'))
                                    ->ip()
                                    ->required(),
                                TextInput::make('user_agent')
                                    ->label(__('campaign_views.user_agent'))
                                    ->maxLength(500),
                                TextInput::make('referrer')
                                    ->label(__('campaign_views.referrer'))
                                    ->url()
                                    ->maxLength(255),
                                TextInput::make('session_id')
                                    ->label(__('campaign_views.session_id'))
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('campaign.name')
                    ->label(__('campaign_views.campaign'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('campaign_views.user'))
                    ->searchable()
                    ->sortable()
                    ->placeholder(__('campaign_views.guest')),
                TextColumn::make('ip_address')
                    ->label(__('campaign_views.ip_address'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user_agent')
                    ->label(__('campaign_views.user_agent'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('referrer')
                    ->label(__('campaign_views.referrer'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('campaign_views.viewed_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('campaign_views.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('campaign_id')
                    ->label(__('campaign_views.campaign'))
                    ->relationship('campaign', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('user_id')
                    ->label(__('campaign_views.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('ip_address')
                    ->label(__('campaign_views.ip_address'))
                    ->options(function () {
                        return CampaignView::distinct('ip_address')
                            ->pluck('ip_address', 'ip_address')
                            ->toArray();
                    })
                    ->searchable(),
            ])
            ->actions([
                // Actions will be handled by pages
            ])
            ->bulkActions([
                // Bulk actions will be handled by pages
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
            'index' => Pages\ListCampaignViews::route('/'),
            'view' => Pages\ViewCampaignView::route('/{record}'),
        ];
    }
}
