<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralCodeUsageLogResource\Pages;
use BackedEnum;
use App\Models\ReferralCode;
use App\Models\ReferralCodeUsageLog;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * ReferralCodeUsageLogResource
 *
 * Filament v4 resource for ReferralCodeUsageLog management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ReferralCodeUsageLogResource extends Resource
{
    protected static ?string $model = ReferralCodeUsageLog::class;

    protected static ?int $navigationSort = 18;

    protected static ?string $recordTitleAttribute = 'ip_address';

    protected static UnitEnum|string|null $navigationGroup = 'Analytics';

    public static function getNavigationLabel(): string
    {
        return __('admin.referral_code_usage_logs.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.referral_code_usage_logs.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.referral_code_usage_logs.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaSection::make(__('admin.referral_code_usage_logs.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('referral_code_id')
                                    ->label(__('admin.referral_code_usage_logs.referral_code'))
                                    ->options(ReferralCode::pluck('code', 'id'))
                                    ->required()
                                    ->searchable(),
                                Select::make('user_id')
                                    ->label(__('admin.referral_code_usage_logs.user'))
                                    ->options(User::pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),
                                TextInput::make('ip_address')
                                    ->label(__('admin.referral_code_usage_logs.ip_address'))
                                    ->ip()
                                    ->maxLength(45),
                                TextInput::make('referrer')
                                    ->label(__('admin.referral_code_usage_logs.referrer'))
                                    ->url()
                                    ->maxLength(255),
                                TextInput::make('user_agent')
                                    ->label(__('admin.referral_code_usage_logs.user_agent'))
                                    ->maxLength(500),
                            ]),
                        Textarea::make('metadata')
                            ->label(__('admin.referral_code_usage_logs.metadata'))
                            ->rows(5)
                            ->helperText(__('admin.referral_code_usage_logs.metadata_help')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('referralCode.code')
                    ->label(__('admin.referral_code_usage_logs.referral_code'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('user.name')
                    ->label(__('admin.referral_code_usage_logs.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->label(__('admin.referral_code_usage_logs.ip_address'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('referrer')
                    ->label(__('admin.referral_code_usage_logs.referrer'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    }),
                TextColumn::make('user_agent')
                    ->label(__('admin.referral_code_usage_logs.user_agent'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('referral_code_id')
                    ->label(__('admin.referral_code_usage_logs.referral_code'))
                    ->options(ReferralCode::pluck('code', 'id'))
                    ->searchable(),
                SelectFilter::make('user_id')
                    ->label(__('admin.referral_code_usage_logs.user'))
                    ->options(User::pluck('name', 'id'))
                    ->searchable(),
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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListReferralCodeUsageLogs::route('/'),
            'create' => Pages\CreateReferralCodeUsageLog::route('/create'),
            'view' => Pages\ViewReferralCodeUsageLog::route('/{record}'),
            'edit' => Pages\EditReferralCodeUsageLog::route('/{record}/edit'),
        ];
    }
}
