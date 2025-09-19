<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CampaignClickResource\Pages;
use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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
     * @param Forms\Form $schema
     * @return Forms\Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
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
                                ->maxLength(255)
                                ->disabled(),
                        ]),
                    Select::make('customer_id')
                        ->label(__('campaign_clicks.customer'))
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state) {
                                $user = User::find($state);
                                if ($user) {
                                    $set('customer_name', $user->name);
                                    $set('customer_email', $user->email);
                                }
                            }
                        }),
                    TextInput::make('customer_name')
                        ->label(__('campaign_clicks.customer_name'))
                        ->maxLength(255)
                        ->disabled(),
                ]),
            Section::make(__('campaign_clicks.click_information'))
                ->schema([
                    TextInput::make('clicked_url')
                        ->label(__('campaign_clicks.clicked_url'))
                        ->url()
                        ->maxLength(500)
                        ->helperText(__('campaign_clicks.clicked_url_help')),
                    TextInput::make('referer')
                        ->label(__('campaign_clicks.referer'))
                        ->helperText(__('campaign_clicks.referer_help')),
                    TextInput::make('ip_address')
                        ->label(__('campaign_clicks.ip_address'))
                        ->maxLength(45)
                        ->rules(['ip'])
                        ->helperText(__('campaign_clicks.ip_address_help')),
                    TextInput::make('user_agent')
                        ->label(__('campaign_clicks.user_agent'))
                        ->helperText(__('campaign_clicks.user_agent_help')),
                    TextInput::make('device_type')
                        ->label(__('campaign_clicks.device_type'))
                        ->maxLength(50)
                        ->helperText(__('campaign_clicks.device_type_help')),
                    TextInput::make('browser')
                        ->label(__('campaign_clicks.browser'))
                        ->maxLength(100)
                        ->helperText(__('campaign_clicks.browser_help')),
                    TextInput::make('os')
                        ->label(__('campaign_clicks.os'))
                        ->helperText(__('campaign_clicks.os_help')),
                    TextInput::make('country')
                        ->label(__('campaign_clicks.country'))
                        ->helperText(__('campaign_clicks.country_help')),
                ]),
            Section::make(__('campaign_clicks.tracking_information'))
                ->schema([
                    TextInput::make('session_id')
                        ->label(__('campaign_clicks.session_id'))
                        ->helperText(__('campaign_clicks.session_id_help')),
                    TextInput::make('utm_source')
                        ->label(__('campaign_clicks.utm_source'))
                        ->helperText(__('campaign_clicks.utm_source_help')),
                    TextInput::make('utm_medium')
                        ->label(__('campaign_clicks.utm_medium'))
                        ->helperText(__('campaign_clicks.utm_medium_help')),
                    TextInput::make('utm_campaign')
                        ->label(__('campaign_clicks.utm_campaign'))
                        ->helperText(__('campaign_clicks.utm_campaign_help')),
                    TextInput::make('utm_term')
                        ->label(__('campaign_clicks.utm_term'))
                        ->helperText(__('campaign_clicks.utm_term_help')),
                    TextInput::make('utm_content')
                        ->label(__('campaign_clicks.utm_content'))
                        ->helperText(__('campaign_clicks.utm_content_help')),
                ]),
            Section::make(__('campaign_clicks.settings'))
                ->schema([
                    Toggle::make('is_converted')
                        ->label(__('campaign_clicks.is_converted'))
                        ->default(false),
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
                    ->copyable()
                    ->badge()
                    ->color('blue'),
                TextColumn::make('customer.name')
                    ->label(__('campaign_clicks.customer'))
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('clicked_url')
                    ->label(__('campaign_clicks.clicked_url'))
                    ->limit(50),
                TextColumn::make('ip_address')
                    ->label(__('campaign_clicks.ip_address'))
                    ->color('gray'),
                TextColumn::make('device_type')
                    ->label(__('campaign_clicks.device_type'))
                    ->color('purple'),
                TextColumn::make('browser')
                    ->label(__('campaign_clicks.browser'))
                    ->limit(30),
                TextColumn::make('os')
                    ->label(__('campaign_clicks.os')),
                TextColumn::make('country')
                    ->label(__('campaign_clicks.country'))
                    ->color('green'),
                TextColumn::make('utm_source')
                    ->label(__('campaign_clicks.utm_source'))
                    ->color('orange'),
                TextColumn::make('utm_medium')
                    ->label(__('campaign_clicks.utm_medium'))
                    ->color('teal'),
                TextColumn::make('utm_campaign')
                    ->label(__('campaign_clicks.utm_campaign'))
                    ->color('indigo'),
                IconColumn::make('is_converted')
                    ->label(__('campaign_clicks.is_converted'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('conversion_value')
                    ->label(__('campaign_clicks.conversion_value'))
                    ->money('EUR')
                    ->alignCenter(),
                TextColumn::make('conversion_currency')
                    ->label(__('campaign_clicks.conversion_currency')),
                TextColumn::make('session_id')
                    ->label(__('campaign_clicks.session_id'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('clicked_at')
                    ->label(__('campaign_clicks.clicked_at'))
                    ->dateTime()
                    ->sortable(),
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
                    ->preload(),
                SelectFilter::make('customer_id')
                    ->relationship('customer', 'name')
                    ->preload(),
                TernaryFilter::make('is_converted')
                    ->trueLabel(__('campaign_clicks.conversions_only'))
                    ->falseLabel(__('campaign_clicks.non_conversions_only'))
                    ->native(false),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('mark_conversion')
                    ->label(__('campaign_clicks.mark_conversion'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(CampaignClick $record): bool => !$record->is_converted)
                    ->action(function (CampaignClick $record): void {
                        $record->update(['is_converted' => true]);

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
                    ->visible(fn(CampaignClick $record): bool => $record->is_converted)
                    ->action(function (CampaignClick $record): void {
                        $record->update(['is_converted' => false]);

                        Notification::make()
                            ->title(__('campaign_clicks.unmarked_as_conversion_successfully'))
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
                            $records->each->update(['is_converted' => true]);
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
                            $records->each->update(['is_converted' => false]);
                            Notification::make()
                                ->title(__('campaign_clicks.bulk_unmarked_as_conversions_success'))
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
