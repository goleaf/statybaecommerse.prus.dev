<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ReferralCodeResource\Pages;
use App\Models\ReferralCode;
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

final class ReferralCodeResource extends Resource
{
    protected static ?string $model = ReferralCode::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?int $navigationSort = 16;
    protected static ?string $recordTitleAttribute = 'code';

    public static function getNavigationGroup(): string
    {
        return NavigationGroup::Referrals->getLabel();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->schema([
                Section::make('Code Details')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required(),
                        TextInput::make('code')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->maxLength(65535)
                            ->nullable(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->inline(false)
                            ->default(true),
                        DatePicker::make('expires_at')
                            ->nullable(),
                        TextInput::make('usage_limit')
                            ->numeric()
                            ->integer()
                            ->nullable(),
                        TextInput::make('usage_count')
                            ->numeric()
                            ->integer()
                            ->default(0),
                        TextInput::make('reward_amount')
                            ->numeric()
                            ->default(0.00),
                        Select::make('reward_type')
                            ->options([
                                'discount' => 'Discount',
                                'credit' => 'Credit',
                                'points' => 'Points',
                                'gift' => 'Gift',
                            ])
                            ->nullable(),
                        TextInput::make('campaign_id')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('source')
                            ->maxLength(255)
                            ->nullable(),
                        KeyValue::make('conditions')
                            ->label('Conditions (JSON)')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->reorderable()
                            ->addActionLabel('Add Condition')
                            ->columnSpanFull(),
                        KeyValue::make('tags')
                            ->label('Tags (JSON)')
                            ->keyLabel('Tag')
                            ->valueLabel('Value')
                            ->reorderable()
                            ->addActionLabel('Add Tag')
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
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usage_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('usage_limit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reward_amount')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('reward_type')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                TextColumn::make('expires_at')
                    ->dateTime()
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
                SelectFilter::make('user_id')
                    ->relationship('user', 'name'),
                SelectFilter::make('campaign_id')
                    ->options(ReferralCode::distinct()->pluck('campaign_id', 'campaign_id')->toArray()),
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
            'index' => Pages\ListReferralCodes::route('/'),
            'create' => Pages\CreateReferralCode::route('/create'),
            'view' => Pages\ViewReferralCode::route('/{record}'),
            'edit' => Pages\EditReferralCode::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'title', 'description', 'campaign_id', 'source'];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::$model::count();
    }
}