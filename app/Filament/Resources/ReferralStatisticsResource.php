<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ReferralStatisticsResource\Pages;
use App\Models\ReferralStatistics;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;

final class ReferralStatisticsResource extends Resource
{
    protected static ?string $model = ReferralStatistics::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?int $navigationSort = 14;
    protected static ?string $recordTitleAttribute = 'date';

    public static function getNavigationGroup(): string
    {
        return NavigationGroup::Referrals->getLabel();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->schema([
                Section::make('Statistics Details')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required(),
                        DatePicker::make('date')
                            ->required(),
                        TextInput::make('total_referrals')
                            ->numeric()
                            ->integer()
                            ->default(0),
                        TextInput::make('completed_referrals')
                            ->numeric()
                            ->integer()
                            ->default(0),
                        TextInput::make('pending_referrals')
                            ->numeric()
                            ->integer()
                            ->default(0),
                        TextInput::make('total_rewards_earned')
                            ->numeric()
                            ->default(0.00),
                        TextInput::make('total_discounts_given')
                            ->numeric()
                            ->default(0.00),
                        KeyValue::make('metadata')
                            ->label('Metadata (JSON)')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->reorderable()
                            ->addActionLabel('Add Metadata Item')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('total_referrals')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('completed_referrals')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pending_referrals')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_rewards_earned')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('total_discounts_given')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->relationship('user', 'name'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => Pages\ListReferralStatistics::route('/'),
            'create' => Pages\CreateReferralStatistics::route('/create'),
            'view' => Pages\ViewReferralStatistics::route('/{record}'),
            'edit' => Pages\EditReferralStatistics::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['date', 'metadata'];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::$model::count();
    }
}