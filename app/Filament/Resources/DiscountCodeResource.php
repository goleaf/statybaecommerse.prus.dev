<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountCodeResource\Pages;
use App\Filament\Resources\DiscountCodeResource\RelationManagers;
use App\Models\DiscountCode;
use App\Services\MultiLanguageTabService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ProgressEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class DiscountCodeResource extends Resource
{
    protected static ?string $model = DiscountCode::class;

    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-ticket';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.marketing');
    }

    public static function getNavigationLabel(): string
    {
        return __('discount_codes');
    }

    public static function getModelLabel(): string
    {
        return __('discount_code');
    }

    public static function getPluralModelLabel(): string
    {
        return __('discount_codes');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('admin.tabs.general'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('code')
                                    ->label(__('discount_code_code'))
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(ignoreRecord: true)
                                    ->suffixAction(
                                        \Filament\Forms\Components\Actions\Action::make('generate')
                                            ->icon('heroicon-m-sparkles')
                                            ->action(function ($state, $set) {
                                                $set('code', DiscountCode::generateUniqueCode());
                                            })
                                    ),
                                Select::make('discount_id')
                                    ->label(__('discount_code_discount'))
                                    ->relationship('discount', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('slug')
                                            ->required()
                                            ->maxLength(255),
                                    ]),
                            ]),

                        ...MultiLanguageTabService::createSimpleTabs([
                            'description' => [
                                'type' => 'textarea',
                                'label' => __('discount_code_description'),
                                'required' => false,
                                'rows' => 3,
                            ],
                        ]),
                    ]),

                Section::make(__('admin.tabs.settings'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('starts_at')
                                    ->label(__('discount_code_starts_at'))
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i'),
                                DateTimePicker::make('expires_at')
                                    ->label(__('discount_code_expires_at'))
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->after('starts_at'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('usage_limit')
                                    ->label(__('discount_code_usage_limit'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText(__('admin.help.usage_limit')),
                                TextInput::make('usage_limit_per_user')
                                    ->label(__('discount_code_usage_limit_per_user'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText(__('admin.help.usage_limit_per_user')),
                                Select::make('status')
                                    ->label(__('discount_code_status'))
                                    ->options([
                                        'active' => __('discount_code_active'),
                                        'inactive' => __('discount_code_inactive'),
                                        'scheduled' => __('discount_code_scheduled'),
                                        'expired' => __('discount_code_expired'),
                                    ])
                                    ->default('active')
                                    ->required(),
                            ]),

                        Toggle::make('is_active')
                            ->label(__('discount_code_is_active'))
                            ->default(true)
                            ->helperText(__('admin.help.is_active')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('discount_code_code'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight(FontWeight::Bold)
                    ->color('primary'),

                Tables\Columns\TextColumn::make('discount.name')
                    ->label(__('discount_code_discount'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('discount_code_description'))
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('usage_count')
                    ->label(__('discount_code_usage_count'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state, $record) => $record->hasReachedLimit() ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('usage_limit')
                    ->label(__('discount_code_usage_limit'))
                    ->numeric()
                    ->sortable()
                    ->placeholder(__('Unlimited')),

                Tables\Columns\TextColumn::make('remaining_uses')
                    ->label(__('discount_code_remaining_uses'))
                    ->numeric()
                    ->sortable()
                    ->placeholder(__('Unlimited'))
                    ->color(fn ($state) => $state && $state <= 5 ? 'warning' : 'success'),

                Tables\Columns\ProgressColumn::make('usage_percentage')
                    ->label(__('discount_code_usage_percentage'))
                    ->color(fn ($state) => match (true) {
                        $state >= 90 => 'danger',
                        $state >= 70 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('discount_code_is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('discount_code_status'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'expired' => 'danger',
                        'scheduled' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label(__('discount_code_starts_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder(__('Immediately')),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label(__('discount_code_expires_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder(__('Never'))
                    ->color(fn ($state) => $state && $state < now()->addDays(7) ? 'warning' : null),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label(__('discount_code_created_by'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.table.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('discount_id')
                    ->label(__('discount_code_discount'))
                    ->relationship('discount', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label(__('discount_code_status'))
                    ->options([
                        'active' => __('discount_code_active'),
                        'inactive' => __('discount_code_inactive'),
                        'scheduled' => __('discount_code_scheduled'),
                        'expired' => __('discount_code_expired'),
                    ]),

                TernaryFilter::make('is_active')
                    ->label(__('discount_code_is_active')),

                Filter::make('expiring_soon')
                    ->label(__('discount_code_expiring_soon'))
                    ->query(fn (Builder $query): Builder => $query->where('expires_at', '<=', now()->addDays(7))),

                Filter::make('usage_limit_reached')
                    ->label(__('admin.filters.usage_limit_reached'))
                    ->query(fn (Builder $query): Builder => $query->usageLimitReached()),

                Filter::make('created_today')
                    ->label(__('admin.filters.created_today'))
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today())),
            ])
            ->actions([
                Action::make('copy')
                    ->label(__('discount_code_copy'))
                    ->icon('heroicon-m-clipboard')
                    ->action(function (DiscountCode $record) {
                        return $record->code;
                    })
                    ->requiresConfirmation(false),

                Action::make('validate')
                    ->label(__('discount_code_validate'))
                    ->icon('heroicon-m-check-circle')
                    ->color(fn (DiscountCode $record) => $record->isValid() ? 'success' : 'danger')
                    ->action(function (DiscountCode $record) {
                        return $record->isValid()
                            ? __('discount_code_success')
                            : __('discount_code_invalid');
                    }),

                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(__('admin.actions.enable'))
                        ->icon('heroicon-m-check')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => true, 'status' => 'active']);
                        }),

                    BulkAction::make('deactivate')
                        ->label(__('admin.actions.disable'))
                        ->icon('heroicon-m-x-mark')
                        ->color('danger')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => false, 'status' => 'inactive']);
                        }),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RedemptionsRelationManager::class,
            RelationManagers\OrdersRelationManager::class,
            RelationManagers\UsersRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make(__('admin.tabs.general'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('code')
                                    ->label(__('discount_code_code'))
                                    ->weight(FontWeight::Bold)
                                    ->color('primary')
                                    ->copyable(),

                                TextEntry::make('discount.name')
                                    ->label(__('discount_code_discount')),
                            ]),

                        TextEntry::make('description')
                            ->label(__('discount_code_description'))
                            ->columnSpanFull(),
                    ]),

                InfolistSection::make(__('admin.tabs.settings'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('status')
                                    ->label(__('discount_code_status'))
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'active' => 'success',
                                        'inactive' => 'gray',
                                        'expired' => 'danger',
                                        'scheduled' => 'warning',
                                        default => 'gray',
                                    }),

                                IconEntry::make('is_active')
                                    ->label(__('discount_code_is_active'))
                                    ->boolean(),

                                TextEntry::make('usage_count')
                                    ->label(__('discount_code_usage_count'))
                                    ->badge()
                                    ->color(fn ($state, $record) => $record->hasReachedLimit() ? 'danger' : 'success'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('starts_at')
                                    ->label(__('discount_code_starts_at'))
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder(__('Immediately')),

                                TextEntry::make('expires_at')
                                    ->label(__('discount_code_expires_at'))
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder(__('Never'))
                                    ->color(fn ($state) => $state && $state < now()->addDays(7) ? 'warning' : null),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('usage_limit')
                                    ->label(__('discount_code_usage_limit'))
                                    ->placeholder(__('Unlimited')),

                                TextEntry::make('usage_limit_per_user')
                                    ->label(__('discount_code_usage_limit_per_user'))
                                    ->placeholder(__('Unlimited')),

                                TextEntry::make('remaining_uses')
                                    ->label(__('discount_code_remaining_uses'))
                                    ->placeholder(__('Unlimited'))
                                    ->color(fn ($state) => $state && $state <= 5 ? 'warning' : 'success'),
                            ]),

                        ProgressEntry::make('usage_percentage')
                            ->label(__('discount_code_usage_percentage'))
                            ->color(fn ($state) => match (true) {
                                $state >= 90 => 'danger',
                                $state >= 70 => 'warning',
                                default => 'success',
                            }),
                    ]),

                InfolistSection::make(__('admin.tabs.metadata'))
                    ->schema([
                        TextEntry::make('creator.name')
                            ->label(__('discount_code_created_by')),

                        TextEntry::make('updater.name')
                            ->label(__('discount_code_updated_by')),

                        TextEntry::make('created_at')
                            ->label(__('admin.table.created_at'))
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('updated_at')
                            ->label(__('admin.table.updated_at'))
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscountCodes::route('/'),
            'create' => Pages\CreateDiscountCode::route('/create'),
            'view' => Pages\ViewDiscountCode::route('/{record}'),
            'edit' => Pages\EditDiscountCode::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\DiscountCodeStatsWidget::class,
            \App\Filament\Widgets\DiscountCodeUsageChartWidget::class,
            \App\Filament\Widgets\RecentDiscountCodeActivityWidget::class,
        ];
    }
}
