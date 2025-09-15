<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use App\Enums\NavigationGroup;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * CompanyResource
 * 
 * Filament v4 resource for Company management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;
    
    /** @var UnitEnum|string|null */
        protected static string | UnitEnum | null $navigationGroup = NavigationGroup::
    
    protected static ?int $navigationSort = 8;
    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('companies.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::System->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('companies.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('companies.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('companies.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('companies.name'))
                                ->required()
                                ->maxLength(255),
                            
                            TextInput::make('legal_name')
                                ->label(__('companies.legal_name'))
                                ->maxLength(255),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('registration_number')
                                ->label(__('companies.registration_number'))
                                ->maxLength(50),
                            
                            TextInput::make('tax_number')
                                ->label(__('companies.tax_number'))
                                ->maxLength(50),
                        ]),
                    
                    Textarea::make('description')
                        ->label(__('companies.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('companies.contact_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('email')
                                ->label(__('companies.email'))
                                ->email()
                                ->maxLength(255),
                            
                            TextInput::make('phone')
                                ->label(__('companies.phone'))
                                ->tel()
                                ->maxLength(20),
                        ]),
                    
                    TextInput::make('website')
                        ->label(__('companies.website'))
                        ->url()
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('companies.address_information'))
                ->schema([
                    KeyValue::make('address')
                        ->label(__('companies.address'))
                        ->keyLabel(__('companies.address_field'))
                        ->valueLabel(__('companies.address_value'))
                        ->addActionLabel(__('companies.add_address_field')),
                ]),
            
            Section::make(__('companies.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('companies.is_active'))
                                ->default(true),
                            
                            Toggle::make('is_default')
                                ->label(__('companies.is_default')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('sort_order')
                                ->label(__('companies.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                            
                            Select::make('type')
                                ->label(__('companies.type'))
                                ->options([
                                    'main' => __('companies.types.main'),
                                    'subsidiary' => __('companies.types.subsidiary'),
                                    'partner' => __('companies.types.partner'),
                                    'supplier' => __('companies.types.supplier'),
                                ])
                                ->default('main'),
                        ]),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('companies.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('legal_name')
                    ->label(__('companies.legal_name'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('registration_number')
                    ->label(__('companies.registration_number'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('tax_number')
                    ->label(__('companies.tax_number'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('email')
                    ->label(__('companies.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('phone')
                    ->label(__('companies.phone'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('website')
                    ->label(__('companies.website'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('type')
                    ->label(__('companies.type'))
                    ->formatStateUsing(fn (string $state): string => __("companies.types.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'main' => 'blue',
                        'subsidiary' => 'green',
                        'partner' => 'purple',
                        'supplier' => 'orange',
                        default => 'gray',
                    }),
                
                IconColumn::make('is_active')
                    ->label(__('companies.is_active'))
                    ->boolean()
                    ->sortable(),
                
                IconColumn::make('is_default')
                    ->label(__('companies.is_default'))
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('sort_order')
                    ->label(__('companies.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label(__('companies.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('companies.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('companies.type'))
                    ->options([
                        'main' => __('companies.types.main'),
                        'subsidiary' => __('companies.types.subsidiary'),
                        'partner' => __('companies.types.partner'),
                        'supplier' => __('companies.types.supplier'),
                    ]),
                
                TernaryFilter::make('is_active')
                    ->label(__('companies.is_active'))
                    ->boolean()
                    ->trueLabel(__('companies.active_only'))
                    ->falseLabel(__('companies.inactive_only'))
                    ->native(false),
                
                TernaryFilter::make('is_default')
                    ->label(__('companies.is_default'))
                    ->boolean()
                    ->trueLabel(__('companies.default_only'))
                    ->falseLabel(__('companies.non_default_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                TableAction::make('toggle_active')
                    ->label(fn (Company $record): string => $record->is_active ? __('companies.deactivate') : __('companies.activate'))
                    ->icon(fn (Company $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Company $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Company $record): void {
                        $record->update(['is_active' => !$record->is_active]);
                        
                        Notification::make()
                            ->title($record->is_active ? __('companies.activated_successfully') : __('companies.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                TableAction::make('set_default')
                    ->label(__('companies.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (Company $record): bool => !$record->is_default)
                    ->action(function (Company $record): void {
                        // Remove default from other companies
                        Company::where('is_default', true)->update(['is_default' => false]);
                        
                        // Set this company as default
                        $record->update(['is_default' => true]);
                        
                        Notification::make()
                            ->title(__('companies.set_as_default_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    BulkAction::make('activate')
                        ->label(__('companies.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            
                            Notification::make()
                                ->title(__('companies.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('deactivate')
                        ->label(__('companies.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            
                            Notification::make()
                                ->title(__('companies.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'view' => Pages\ViewCompany::route('/{record}'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
