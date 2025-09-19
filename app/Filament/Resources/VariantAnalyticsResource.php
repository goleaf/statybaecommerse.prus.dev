<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VariantAnalyticsResource\Pages;
use App\Models\VariantAnalytics;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\DateFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use App\Enums\NavigationGroup;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
final class VariantAnalyticsResource extends Resource
{
    protected static ?string $model = VariantAnalytics::class;
    
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';
    

    // protected static $navigationGroup = NavigationGroup::Analytics;
    
    protected static ?int $navigationSort = 2;
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament::variant_analytics.basic_info'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('variant_id')
                                    ->label(__('filament::variant_analytics.variant'))
                                    ->relationship('variant', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(1),
                                
                                DatePicker::make('date')
                                    ->label(__('filament::variant_analytics.date'))
                                    ->required()
                                    ->default(now())
                                    ->columnSpan(1),
                            ]),
                    ]),
                
                Section::make(__('filament::variant_analytics.metrics'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('views')
                                    ->label(__('filament::variant_analytics.views'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->columnSpan(1),
                                
                                TextInput::make('clicks')
                                    ->label(__('filament::variant_analytics.clicks'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->columnSpan(1),
                                
                                TextInput::make('add_to_cart')
                                    ->label(__('filament::variant_analytics.add_to_cart'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->columnSpan(1),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                TextInput::make('purchases')
                                    ->label(__('filament::variant_analytics.purchases'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->columnSpan(1),
                                
                                TextInput::make('revenue')
                                    ->label(__('filament::variant_analytics.revenue'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.0001)
                                    ->default(0)
                                    ->columnSpan(1),
                                
                                TextInput::make('conversion_rate')
                                    ->label(__('filament::variant_analytics.conversion_rate'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.0001)
                                    ->suffix('%')
                                    ->default(0)
                                    ->columnSpan(1),
                            ]),
                    ]),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('variant.name')
                    ->label(__('filament::variant_analytics.variant'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('date')
                    ->label(__('filament::variant_analytics.date'))
                    ->date()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('views')
                    ->label(__('filament::variant_analytics.views'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('clicks')
                    ->label(__('filament::variant_analytics.clicks'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('add_to_cart')
                    ->label(__('filament::variant_analytics.add_to_cart'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('purchases')
                    ->label(__('filament::variant_analytics.purchases'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('revenue')
                    ->label(__('filament::variant_analytics.revenue'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('conversion_rate')
                    ->label(__('filament::variant_analytics.conversion_rate'))
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                    ->sortable()
                    ->toggleable(),
                
                // Calculated metrics
                TextColumn::make('click_through_rate')
                    ->label(__('filament::variant_analytics.ctr'))
                    ->getStateUsing(fn ($record) => $record->click_through_rate)
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                    ->sortable(false),
                
                TextColumn::make('add_to_cart_rate')
                    ->label(__('filament::variant_analytics.atc_rate'))
                    ->getStateUsing(fn ($record) => $record->add_to_cart_rate)
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                    ->sortable(false),
                
                TextColumn::make('purchase_rate')
                    ->label(__('filament::variant_analytics.purchase_rate'))
                    ->getStateUsing(fn ($record) => $record->purchase_rate)
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                    ->sortable(false),
                
                TextColumn::make('average_revenue_per_purchase')
                    ->label(__('filament::variant_analytics.avg_revenue'))
                    ->getStateUsing(fn ($record) => $record->average_revenue_per_purchase)
                    ->money('EUR')
                    ->sortable(false),
            ])
            ->filters([
                SelectFilter::make('variant_id')
                    ->label(__('filament::variant_analytics.variant'))
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->preload(),
                
                DateFilter::make('date')
                    ->label(__('filament::variant_analytics.date')),
                
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('date_from')
                            ->label(__('filament::variant_analytics.date_from')),
                        DatePicker::make('date_until')
                            ->label(__('filament::variant_analytics.date_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
                
                Filter::make('high_performing')
                    ->label(__('filament::variant_analytics.high_performing'))
                    ->query(fn (Builder $query): Builder => $query->where('conversion_rate', '>=', 5.0)),
                
                Filter::make('low_performing')
                    ->label(__('filament::variant_analytics.low_performing'))
                    ->query(fn (Builder $query): Builder => $query->where('conversion_rate', '<', 1.0)),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc')
            ->poll('30s');
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
            'index' => Pages\ListVariantAnalytics::route('/'),
            'create' => Pages\CreateVariantAnalytics::route('/create'),
            'view' => Pages\ViewVariantAnalytics::route('/{record}'),
            'edit' => Pages\EditVariantAnalytics::route('/{record}/edit'),
        ];
    }
}
