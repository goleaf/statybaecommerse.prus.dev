<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

final class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Users';
    }

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Customers';

    protected static ?string $modelLabel = 'Customer';

    protected static ?string $pluralModelLabel = 'Customers';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('customers.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('customers.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('customers.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('customers.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('email')
                                ->label(__('customers.email'))
                                ->email()
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('phone')
                                ->label(__('customers.phone'))
                                ->tel()
                                ->maxLength(20),
                            TextInput::make('address')
                                ->label(__('customers.address'))
                                ->maxLength(500),
                        ]),
                    Textarea::make('description')
                        ->label(__('customers.description'))
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull(),
                ]),
            Section::make(__('customers.location'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Select::make('country_id')
                                ->label(__('customers.country'))
                                ->relationship('country', 'name')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $set('city_id', null);
                                    }
                                }),
                            Select::make('city_id')
                                ->label(__('customers.city'))
                                ->relationship('city', 'name')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->modifyOptionsQueryUsing(function (Builder $query, Forms\Get $get) {
                                    $countryId = $get('country_id');
                                    if ($countryId) {
                                        $query->where('country_id', $countryId);
                                    }
                                }),
                            TextInput::make('postal_code')
                                ->label(__('customers.postal_code'))
                                ->maxLength(20),
                        ]),
                ]),
            Section::make(__('customers.company'))
                ->schema([
                    Select::make('company_id')
                        ->label(__('customers.company'))
                        ->relationship('company', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                ]),
            Section::make(__('customers.settings'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('customers.is_active'))
                        ->default(true),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('customers.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('email')
                    ->label(__('customers.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('phone')
                    ->label(__('customers.phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('address')
                    ->label(__('customers.address'))
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('country.name')
                    ->label(__('customers.country'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('city.name')
                    ->label(__('customers.city'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('company.name')
                    ->label(__('customers.company'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('postal_code')
                    ->label(__('customers.postal_code'))
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('customers.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('orders_count')
                    ->label(__('customers.orders_count'))
                    ->counts('orders')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('customers.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('customers.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('country_id')
                    ->relationship('country', 'name')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('city_id')
                    ->relationship('city', 'name')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('company_id')
                    ->relationship('company', 'name')
                    ->preload()
                    ->searchable(),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('customers.active_only'))
                    ->falseLabel(__('customers.inactive_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn (Customer $record): string => $record->is_active ? __('customers.deactivate') : __('customers.activate'))
                    ->icon(fn (Customer $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Customer $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Customer $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? __('customers.activated_successfully') : __('customers.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('customers.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('customers.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('customers.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('customers.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Get the relations for this resource.
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
