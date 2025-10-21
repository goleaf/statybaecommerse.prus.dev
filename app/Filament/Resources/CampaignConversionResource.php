<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CampaignConversionResource\Pages;
use App\Models\Campaign;
use App\Models\CampaignConversion;
use App\Models\User;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Number;
use UnitEnum;

/**
 * CampaignConversionResource
 *
 * Filament v4 resource for CampaignConversion management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class CampaignConversionResource extends Resource
{
    protected static ?string $model = CampaignConversion::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rocket-launch';

    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::Campaigns;

    public static function getNavigationLabel(): string
    {
        return __('campaign_conversions.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return self::$navigationGroup instanceof NavigationGroup
            ? self::$navigationGroup->label()
            : self::$navigationGroup;
    }

    public static function getPluralModelLabel(): string
    {
        return __('campaign_conversions.plural');
    }

    public static function getModelLabel(): string
    {
        return __('campaign_conversions.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {

        $form = $schema; // Preserve legacy variable naming for existing schema definitions.

        return $form->schema([
            Section::make(__('campaign_conversions.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('campaign_id')
                                ->label(__('campaign_conversions.campaign'))
                                ->relationship('campaign', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set): void {
                                    if ($state) {
                                        $campaign = Campaign::find($state);
                                        if ($campaign) {
                                            $set('campaign_name', $campaign->name);
                                            $set('campaign_code', $campaign->code);
                                        }
                                    }
                                }),
                            TextInput::make('campaign_name')
                                ->label(__('campaign_conversions.campaign_name'))
                                ->maxLength(255)
                                ->disabled(),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Select::make('customer_id')
                                ->label(__('campaign_conversions.user'))
                                ->relationship('customer', 'name')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set): void {
                                    if ($state) {
                                        $user = User::find($state);
                                        if ($user) {
                                            $set('user_name', $user->name);
                                            $set('user_email', $user->email);
                                        }
                                    }
                                }),
                            TextInput::make('user_name')
                                ->label(__('campaign_conversions.user_name'))
                                ->maxLength(255)
                                ->disabled(),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('conversion_value')
                                ->label(__('campaign_conversions.conversion_value'))
                                ->numeric()
                                ->prefix('€')
                                ->step(0.01)
                                ->minValue(0),
                            TextInput::make('conversion_currency')
                                ->label(__('campaign_conversions.conversion_currency'))
                                ->maxLength(3)
                                ->default('EUR'),
                        ]),
                    Toggle::make('is_converted')
                        ->label(__('campaign_conversions.is_converted'))
                        ->default(false),
                    Textarea::make('notes')
                        ->label(__('campaign_conversions.notes'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('campaign.name')
                    ->label(__('campaign_conversions.campaign'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label(__('campaign_conversions.user'))
                    ->sortable(),
                IconColumn::make('is_converted')
                    ->label(__('campaign_conversions.is_converted'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('conversion_value')
                    ->label(__('campaign_conversions.conversion_value'))
                    ->formatStateUsing(fn ($state, CampaignConversion $record) => Number::currency((float) $state, $record->conversion_currency ?? 'EUR', locale: app()->getLocale()))
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label(__('campaign_conversions.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('campaign_id')
                    ->label(__('campaign_conversions.campaign'))
                    ->relationship('campaign', 'name')
                    ->preload(),
                TernaryFilter::make('is_converted')
                    ->trueLabel(__('campaign_conversions.conversions_only'))
                    ->falseLabel(__('campaign_conversions.non_conversions_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCampaignConversions::route('/'),
            'create' => Pages\CreateCampaignConversion::route('/create'),
            'edit'   => Pages\EditCampaignConversion::route('/{record}/edit'),
            'view'   => Pages\ViewCampaignConversion::route('/{record}'),
        ];
    }
}
