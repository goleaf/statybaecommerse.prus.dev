<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignClickResource\Pages;
use App\Models\CampaignClick;
use App\Models\Campaign;
use App\Models\User;
use App\Enums\NavigationGroup;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Form;

/**
 * CampaignClickResource
 * 
 * Filament v4 resource for CampaignClick management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class CampaignClickResource extends Resource
{
    protected static ?string $model = CampaignClick::class;
    
    /** @var UnitEnum|string|null */
    protected static string | UnitEnum | null $navigationGroup = NavigationGroup::Campaigns;
    
    protected static ?int $navigationSort = 8;
    protected static ?string $recordTitleAttribute = 'campaign_name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('campaign_clicks.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Marketing->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('campaign_clicks.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('campaign_clicks.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('campaign_clicks.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('campaign_id')
                                ->label(__('campaign_clicks.campaign'))
                                ->relationship('campaign', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $campaign = Campaign::find($state);
                                        if ($campaign) {
                                            $set('campaign_name', $campaign->name);
                                            $set('campaign_code', $campaign->code);
                                        }
                                    }
                                }),
                            
                            TextInput::make('campaign_name')
                                ->label(__('campaign_clicks.campaign_name'))
                                ->required()
                                ->maxLength(255)
                                ->disabled(),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            Select::make('user_id')
                                ->label(__('campaign_clicks.user'))
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $user = User::find($state);
                                        if ($user) {
                                            $set('user_name', $user->name);
                                            $set('user_email', $user->email);
                                        }
                                    }
                                }),
                            
                            TextInput::make('user_name')
                                ->label(__('campaign_clicks.user_name'))
                                ->maxLength(255)
                                ->disabled(),
                        ]),
                ]),
            
            Section::make(__('campaign_clicks.click_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('click_url')
                                ->label(__('campaign_clicks.click_url'))
                                ->required()
                                ->url()
                                ->maxLength(500)
                                ->helperText(__('campaign_clicks.click_url_help')),
                            
                            TextInput::make('referrer_url')
                                ->label(__('campaign_clicks.referrer_url'))
                                ->url()
                                ->maxLength(500)
                                ->helperText(__('campaign_clicks.referrer_url_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('ip_address')
                                ->label(__('campaign_clicks.ip_address'))
                                ->maxLength(45)
                                ->rules(['ip'])
                                ->helperText(__('campaign_clicks.ip_address_help')),
                            
                            TextInput::make('user_agent')
                                ->label(__('campaign_clicks.user_agent'))
                                ->maxLength(500)
                                ->helperText(__('campaign_clicks.user_agent_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('device_type')
                                ->label(__('campaign_clicks.device_type'))
                                ->maxLength(50)
                                ->helperText(__('campaign_clicks.device_type_help')),
                            
                            TextInput::make('browser')
                                ->label(__('campaign_clicks.browser'))
                                ->maxLength(100)
                                ->helperText(__('campaign_clicks.browser_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('os')
                                ->label(__('campaign_clicks.os'))
                                ->maxLength(100)
                                ->helperText(__('campaign_clicks.os_help')),
                            
                            TextInput::make('country')
                                ->label(__('campaign_clicks.country'))
                                ->maxLength(100)
                                ->helperText(__('campaign_clicks.country_help')),
                        ]),
                ]),
            
            Section::make(__('campaign_clicks.tracking_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('session_id')
                                ->label(__('campaign_clicks.session_id'))
                                ->maxLength(100)
                                ->helperText(__('campaign_clicks.session_id_help')),
                            
                            TextInput::make('utm_source')
                                ->label(__('campaign_clicks.utm_source'))
                                ->maxLength(100)
                                ->helperText(__('campaign_clicks.utm_source_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('utm_medium')
                                ->label(__('campaign_clicks.utm_medium'))
                                ->maxLength(100)
                                ->helperText(__('campaign_clicks.utm_medium_help')),
                            
                            TextInput::make('utm_campaign')
                                ->label(__('campaign_clicks.utm_campaign'))
                                ->maxLength(100)
                                ->helperText(__('campaign_clicks.utm_campaign_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('utm_term')
                                ->label(__('campaign_clicks.utm_term'))
                                ->maxLength(100)
                                ->helperText(__('campaign_clicks.utm_term_help')),
                            
                            TextInput::make('utm_content')
                                ->label(__('campaign_clicks.utm_content'))
                                ->maxLength(100)
                                ->helperText(__('campaign_clicks.utm_content_help')),
                        ]),
                ]),
            
            Section::make(__('campaign_clicks.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_conversion')
                                ->label(__('campaign_clicks.is_conversion'))
                                ->default(false),
                            
                            Toggle::make('is_bot')
                                ->label(__('campaign_clicks.is_bot'))
                                ->default(false),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('conversion_value')
                                ->label(__('campaign_clicks.conversion_value'))
                                ->numeric()
                                ->prefix('€')
                                ->step(0.01)
                                ->minValue(0)
                                ->helperText(__('campaign_clicks.conversion_value_help')),
                            
                            TextInput::make('conversion_currency')
                                ->label(__('campaign_clicks.conversion_currency'))
                                ->maxLength(3)
                                ->default('EUR')
                                ->rules(['alpha']),
                        ]),
                    
                    Textarea::make('notes')
                        ->label(__('campaign_clicks.notes'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('campaign_name')
                    ->label(__('campaign_clicks.campaign_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),
                
                TextColumn::make('campaign_code')
                    ->label(__('campaign_clicks.campaign_code'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('blue'),
                
                TextColumn::make('user.name')
                    ->label(__('campaign_clicks.user'))
                    ->sortable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('click_url')
                    ->label(__('campaign_clicks.click_url'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('ip_address')
                    ->label(__('campaign_clicks.ip_address'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('device_type')
                    ->label(__('campaign_clicks.device_type'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('purple')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('browser')
                    ->label(__('campaign_clicks.browser'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('os')
                    ->label(__('campaign_clicks.os'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('country')
                    ->label(__('campaign_clicks.country'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('green')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('utm_source')
                    ->label(__('campaign_clicks.utm_source'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('orange')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('utm_medium')
                    ->label(__('campaign_clicks.utm_medium'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('teal')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('utm_campaign')
                    ->label(__('campaign_clicks.utm_campaign'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('indigo')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_conversion')
                    ->label(__('campaign_clicks.is_conversion'))
                    ->boolean()
                    ->sortable(),
                
                IconColumn::make('is_bot')
                    ->label(__('campaign_clicks.is_bot'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('conversion_value')
                    ->label(__('campaign_clicks.conversion_value'))
                    ->money('EUR')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('conversion_currency')
                    ->label(__('campaign_clicks.conversion_currency'))
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('session_id')
                    ->label(__('campaign_clicks.session_id'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('clicked_at')
                    ->label(__('campaign_clicks.clicked_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label(__('campaign_clicks.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('campaign_clicks.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('campaign_id')
                    ->label(__('campaign_clicks.campaign'))
                    ->relationship('campaign', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('user_id')
                    ->label(__('campaign_clicks.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                
                TernaryFilter::make('is_conversion')
                    ->label(__('campaign_clicks.is_conversion'))
                    ->boolean()
                    ->trueLabel(__('campaign_clicks.conversions_only'))
                    ->falseLabel(__('campaign_clicks.non_conversions_only'))
                    ->native(false),
                
                TernaryFilter::make('is_bot')
                    ->label(__('campaign_clicks.is_bot'))
                    ->boolean()
                    ->trueLabel(__('campaign_clicks.bots_only'))
                    ->falseLabel(__('campaign_clicks.humans_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                
                Action::make('mark_conversion')
                    ->label(__('campaign_clicks.mark_conversion'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (CampaignClick $record): bool => !$record->is_conversion)
                    ->action(function (CampaignClick $record): void {
                        $record->update(['is_conversion' => true]);
                        
                        Notification::make()
                            ->title(__('campaign_clicks.marked_as_conversion_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                Action::make('unmark_conversion')
                    ->label(__('campaign_clicks.unmark_conversion'))
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn (CampaignClick $record): bool => $record->is_conversion)
                    ->action(function (CampaignClick $record): void {
                        $record->update(['is_conversion' => false]);
                        
                        Notification::make()
                            ->title(__('campaign_clicks.unmarked_as_conversion_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                Action::make('mark_bot')
                    ->label(__('campaign_clicks.mark_bot'))
                    ->icon('heroicon-o-robot')
                    ->color('danger')
                    ->visible(fn (CampaignClick $record): bool => !$record->is_bot)
                    ->action(function (CampaignClick $record): void {
                        $record->update(['is_bot' => true]);
                        
                        Notification::make()
                            ->title(__('campaign_clicks.marked_as_bot_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                Action::make('unmark_bot')
                    ->label(__('campaign_clicks.unmark_bot'))
                    ->icon('heroicon-o-user')
                    ->color('info')
                    ->visible(fn (CampaignClick $record): bool => $record->is_bot)
                    ->action(function (CampaignClick $record): void {
                        $record->update(['is_bot' => false]);
                        
                        Notification::make()
                            ->title(__('campaign_clicks.unmarked_as_bot_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    
                    BulkAction::make('mark_conversions')
                        ->label(__('campaign_clicks.mark_conversions'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_conversion' => true]);
                            
                            Notification::make()
                                ->title(__('campaign_clicks.bulk_marked_as_conversions_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('unmark_conversions')
                        ->label(__('campaign_clicks.unmark_conversions'))
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_conversion' => false]);
                            
                            Notification::make()
                                ->title(__('campaign_clicks.bulk_unmarked_as_conversions_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('mark_bots')
                        ->label(__('campaign_clicks.mark_bots'))
                        ->icon('heroicon-o-robot')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_bot' => true]);
                            
                            Notification::make()
                                ->title(__('campaign_clicks.bulk_marked_as_bots_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('unmark_bots')
                        ->label(__('campaign_clicks.unmark_bots'))
                        ->icon('heroicon-o-user')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_bot' => false]);
                            
                            Notification::make()
                                ->title(__('campaign_clicks.bulk_unmarked_as_bots_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaignClicks::route('/'),
            'create' => Pages\CreateCampaignClick::route('/create'),
            'view' => Pages\ViewCampaignClick::route('/{record}'),
            'edit' => Pages\EditCampaignClick::route('/{record}/edit'),
        ];
    }
}
