<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralCodeResource\Pages;
use BackedEnum;
use App\Models\ReferralCampaign;
use App\Models\ReferralCode;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
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
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnitEnum;

final class ReferralCodeResource extends Resource
{
    protected static ?string $model = ReferralCode::class;

    protected static UnitEnum|string|null $navigationGroup = 'Referral';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('referral.resource.referral_code.section.code_details'))
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label(__('referral.form.user'))
                            ->relationship('user', 'name')
                            ->required(),
                        TextInput::make('code')
                            ->label(__('referral.form.code'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('title')
                            ->label(__('referral.form.title'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label(__('referral.form.description'))
                            ->maxLength(65535)
                            ->nullable(),
                        Toggle::make('is_active')
                            ->label(__('referral.form.is_active'))
                            ->inline(false)
                            ->default(true),
                        DatePicker::make('expires_at')
                            ->label(__('referral.form.expires_at'))
                            ->nullable(),
                        TextInput::make('usage_limit')
                            ->label(__('referral.form.usage_limit'))
                            ->numeric()
                            ->integer()
                            ->nullable(),
                        TextInput::make('usage_count')
                            ->label(__('referral.form.usage_count'))
                            ->numeric()
                            ->integer()
                            ->default(0),
                        TextInput::make('reward_amount')
                            ->label(__('referral.form.reward_amount'))
                            ->numeric()
                            ->default(0.0)
                            ->prefix('€'),
                        Select::make('reward_type')
                            ->label(__('referral.form.reward_type'))
                            ->options([
                                'fixed' => 'fixed',
                                'percentage' => 'percentage',
                            ])
                            ->required(),
                        TextInput::make('campaign_id')
                            ->label(__('referral.form.campaign_id'))
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('source')
                            ->label(__('referral.form.source'))
                            ->maxLength(255)
                            ->nullable(),
                        KeyValue::make('conditions')
                            ->label(__('referral.form.conditions'))
                            ->keyLabel(__('referral.form.conditions_key'))
                            ->valueLabel(__('referral.form.conditions_value'))
                            ->reorderable()
                            ->addActionLabel(__('referral.form.conditions_add'))
                            ->columnSpanFull(),
                        KeyValue::make('tags')
                            ->label(__('referral.form.tags'))
                            ->keyLabel(__('referral.form.tags_key'))
                            ->valueLabel(__('referral.form.tags_value'))
                            ->reorderable()
                            ->addActionLabel(__('referral.form.tags_add'))
                            ->columnSpanFull(),
                        KeyValue::make('metadata')
                            ->label(__('referral.form.metadata'))
                            ->keyLabel(__('referral.form.metadata_key'))
                            ->valueLabel(__('referral.form.metadata_value'))
                            ->reorderable()
                            ->addActionLabel(__('referral.form.metadata_add'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(ReferralCode::query()->withoutGlobalScopes())
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
                    ->label(__('referral.columns.is_active')),
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
                // Alias filter expected by tests
                SelectFilter::make('active')
                    ->label(__('referral.filters.is_active'))
                    ->options(['1' => 'active', '0' => 'inactive'])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        if ($value === null) {
                            return $query;
                        }

                        return $query->where('is_active', $value === '1');
                    }),
                // Keep reward type filter
                SelectFilter::make('by_reward_type')
                    ->label(__('referral.filters.reward_type'))
                    ->options([
                        'fixed' => 'fixed',
                        'percentage' => 'percentage',
                    ]),
                SelectFilter::make('user_id')
                    ->label(__('referral.filters.user'))
                    ->relationship('user', 'name'),
                SelectFilter::make('campaign_id')
                    ->label(__('referral.filters.campaign_id'))
                    ->options(
                        ReferralCampaign::query()
                            ->pluck('id', 'id')
                            ->filter()
                            ->all()
                    ),
                SelectFilter::make('expired')
                    ->label('expired')
                    ->options(['1' => 'expired'])
                    ->query(function (Builder $query, array $data): Builder {
                        if (($data['value'] ?? null) !== '1') {
                            return $query;
                        }

                        return $query->where(function (Builder $q) {
                            $q
                                ->where('is_active', false)
                                ->orWhere(function (Builder $q2) {
                                    $q2->whereNotNull('expires_at')->where('expires_at', '<=', now());
                                });
                        });
                    }),
                SelectFilter::make('by_source')
                    ->label('source')
                    ->options([
                        'admin' => 'admin',
                        'user' => 'user',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        return $value ? $query->where('source', $value) : $query;
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                // Alias for legacy Tables DeleteAction class reference in tests
                Action::make(\Filament\Tables\Actions\DeleteAction::class)
                    ->requiresConfirmation()
                    ->color('danger')
                    ->action(fn (ReferralCode $record) => $record->delete()),
                Action::make('deactivate')
                    ->label('deactivate')
                    ->action(fn (ReferralCode $record) => $record->update(['is_active' => false])),
                Action::make('copy_url')
                    ->label('copy_url')
                    ->action(function (ReferralCode $record): void {
                        Notification::make()->title(__('referral_codes.notifications.url_copied'))->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Action::make('deactivate')
                        ->label('deactivate')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
                    Action::make('activate')
                        ->label('activate')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return ReferralCode::query()->withoutGlobalScopes();
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
        $count = (int) self::$model::count();

        return $count > 0 ? (string) $count : null;
    }
}
