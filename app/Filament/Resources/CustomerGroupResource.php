<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerGroupResource\Pages;
use App\Models\CustomerGroup;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

final class CustomerGroupResource extends Resource
{
    protected static ?string $model = CustomerGroup::class;

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata.
     */
    protected static \UnitEnum|string|null $navigationGroup = 'Customers';

    public static function getNavigationGroup(): \UnitEnum|string|null
    {
        return self::$navigationGroup ?? __('customer_groups.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('customer_groups.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('customer_groups.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('customer_groups.navigation_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('customer_groups.basic_information'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('customer_groups.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('code')
                                ->label(__('customer_groups.code'))
                                ->maxLength(50)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Textarea::make('description')
                        ->label(__('customer_groups.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            SchemaSection::make(__('customer_groups.settings'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('customer_groups.is_active'))
                                ->default(true),
                            Toggle::make('is_default')
                                ->label(__('customer_groups.is_default'))
                                ->default(false),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('type')
                                ->label(__('customer_groups.type'))
                                ->options([
                                    'regular'   => 'Regular',
                                    'vip'       => 'VIP',
                                    'wholesale' => 'Wholesale',
                                    'retail'    => 'Retail',
                                    'corporate' => 'Corporate',
                                ])
                                ->nullable()
                                ->rules([Rule::in(['regular', 'vip', 'wholesale', 'retail', 'corporate'])]),
                            TextInput::make('sort_order')
                                ->label(__('customer_groups.sort_order'))
                                ->numeric()
                                ->default(0),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('customer_groups.table_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label(__('customer_groups.code'))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('customer_groups.is_active'))
                    ->boolean(),
                IconColumn::make('is_default')
                    ->label(__('customer_groups.is_default'))
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label(__('customer_groups.is_active'))
                    ->options([
                        1 => __('customer_groups.enabled_only'),
                        0 => __('customer_groups.disabled_only'),
                    ])
                    ->query(function (Builder $query, $value): Builder {
                        return $query->when($value !== null, function (Builder $innerQuery) use ($value): Builder {
                            $state = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                            if ($state === null) {
                                $state = (bool) $value;
                            }

                            return $innerQuery->where('is_active', $state);
                        });
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn (CustomerGroup $record): string => $record->is_active ? __('customer_groups.deactivate') : __('customer_groups.activate'))
                    ->icon(fn (CustomerGroup $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (CustomerGroup $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (CustomerGroup $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('customer_groups.activated_successfully') : __('customer_groups.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('customer_groups.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->title(__('customer_groups.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('customer_groups.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->title(__('customer_groups.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomerGroups::route('/'),
            'create' => Pages\CreateCustomerGroup::route('/create'),
            'view'   => Pages\ViewCustomerGroup::route('/{record}'),
            'edit'   => Pages\EditCustomerGroup::route('/{record}/edit'),
        ];
    }
}
