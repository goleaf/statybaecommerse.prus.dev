<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralCodeStatisticsResource\Pages;
use BackedEnum;
use App\Models\ReferralCode;
use App\Models\ReferralCodeStatistics;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * ReferralCodeStatisticsResource
 *
 * Filament v4 resource for ReferralCodeStatistics management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ReferralCodeStatisticsResource extends Resource
{
    protected static ?string $model = ReferralCodeStatistics::class;

    protected static ?int $navigationSort = 9;

    protected static ?string $recordTitleAttribute = 'date';

    protected static UnitEnum|string|null $navigationGroup = 'Analytics';

    public static function getNavigationLabel(): string
    {
        return __('admin.referral_code_statistics.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.referral_code_statistics.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.referral_code_statistics.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaSection::make(__('admin.referral_code_statistics.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('referral_code_id')
                                    ->label(__('admin.referral_code_statistics.referral_code'))
                                    ->options(ReferralCode::pluck('code', 'id'))
                                    ->required()
                                    ->searchable(),
                                DatePicker::make('date')
                                    ->label(__('admin.referral_code_statistics.date'))
                                    ->required()
                                    ->default(now()),
                            ]),
                    ]),
                SchemaSection::make(__('admin.referral_code_statistics.metrics'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('total_views')
                                    ->label(__('admin.referral_code_statistics.total_views'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0),
                                TextInput::make('total_clicks')
                                    ->label(__('admin.referral_code_statistics.total_clicks'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0),
                                TextInput::make('total_signups')
                                    ->label(__('admin.referral_code_statistics.total_signups'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0),
                                TextInput::make('total_conversions')
                                    ->label(__('admin.referral_code_statistics.total_conversions'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0),
                                TextInput::make('total_revenue')
                                    ->label(__('admin.referral_code_statistics.total_revenue'))
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->prefix('€')
                                    ->default(0),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('referralCode.code')
                    ->label(__('admin.referral_code_statistics.referral_code'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('date')
                    ->label(__('admin.referral_code_statistics.date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('total_views')
                    ->label(__('admin.referral_code_statistics.total_views'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_clicks')
                    ->label(__('admin.referral_code_statistics.total_clicks'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_signups')
                    ->label(__('admin.referral_code_statistics.total_signups'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_conversions')
                    ->label(__('admin.referral_code_statistics.total_conversions'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_revenue')
                    ->label(__('admin.referral_code_statistics.total_revenue'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('referral_code_id')
                    ->label(__('admin.referral_code_statistics.referral_code'))
                    ->options(ReferralCode::pluck('code', 'id'))
                    ->searchable(),
                DateFilter::make('date')
                    ->label(__('admin.referral_code_statistics.date')),
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
            ->defaultSort('date', 'desc');
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
            'index' => Pages\ListReferralCodeStatistics::route('/'),
            'create' => Pages\CreateReferralCodeStatistics::route('/create'),
            'view' => Pages\ViewReferralCodeStatistics::route('/{record}'),
            'edit' => Pages\EditReferralCodeStatistics::route('/{record}/edit'),
        ];
    }
}
