<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ReferralCampaignResource\Pages;
use App\Models\ReferralCampaign;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;

final class ReferralCampaignResource extends Resource
{
    protected static ?string $model = ReferralCampaign::class;
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?int $navigationSort = 18;
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string
    {
        return NavigationGroup::Referrals->getLabel();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->schema([
                Section::make('Campaign Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->maxLength(65535)
                            ->nullable(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->inline(false)
                            ->default(true),
                        DatePicker::make('start_date')
                            ->required(),
                        DatePicker::make('end_date')
                            ->required(),
                        TextInput::make('reward_amount')
                            ->numeric()
                            ->required()
                            ->default(0.00),
                        Select::make('reward_type')
                            ->options([
                                'discount' => 'Discount',
                                'credit' => 'Credit',
                                'points' => 'Points',
                                'gift' => 'Gift',
                            ])
                            ->required(),
                        TextInput::make('max_referrals_per_user')
                            ->numeric()
                            ->integer()
                            ->nullable(),
                        TextInput::make('max_total_referrals')
                            ->numeric()
                            ->integer()
                            ->nullable(),
                        KeyValue::make('conditions')
                            ->label('Conditions (JSON)')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->reorderable()
                            ->addActionLabel('Add Condition')
                            ->columnSpanFull(),
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
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('reward_amount')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('reward_type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('max_referrals_per_user')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_total_referrals')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
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
                TernaryFilter::make('is_active')
                    ->label('Active')
                    ->boolean(),
                SelectFilter::make('reward_type')
                    ->options([
                        'discount' => 'Discount',
                        'credit' => 'Credit',
                        'points' => 'Points',
                        'gift' => 'Gift',
                    ]),
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
            'index' => Pages\ListReferralCampaigns::route('/'),
            'create' => Pages\CreateReferralCampaign::route('/create'),
            'view' => Pages\ViewReferralCampaign::route('/{record}'),
            'edit' => Pages\EditReferralCampaign::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description', 'reward_type'];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::$model::count();
    }
}