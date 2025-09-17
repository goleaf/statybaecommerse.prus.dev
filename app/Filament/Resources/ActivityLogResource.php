<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use App\Models\User;
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
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * ActivityLogResource
 * 
 * Filament v4 resource for ActivityLog management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;
    
    protected static string | UnitEnum | null $navigationGroup = NavigationGroup::System;
    
    protected static ?int $navigationSort = 9;
    protected static ?string $recordTitleAttribute = 'description';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('activity_logs.title');
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
        return __('activity_logs.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('activity_logs.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('activity_logs.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('user_id')
                                ->label(__('activity_logs.user'))
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $user = User::find($state);
                                        if ($user) {
                                            $set('user_name', $user->name);
                                            $set('user_email', $user->email);
                                        }
                                    }
                                }),
                            
                            TextInput::make('user_name')
                                ->label(__('activity_logs.user_name'))
                                ->maxLength(255)
                                ->disabled(),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('log_name')
                                ->label(__('activity_logs.log_name'))
                                ->maxLength(255)
                                ->helperText(__('activity_logs.log_name_help')),
                            
                            TextInput::make('description')
                                ->label(__('activity_logs.description'))
                                ->required()
                                ->maxLength(255)
                                ->helperText(__('activity_logs.description_help')),
                        ]),
                ]),
            
            Section::make(__('activity_logs.event_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('event')
                                ->label(__('activity_logs.event'))
                                ->options([
                                    'created' => __('activity_logs.events.created'),
                                    'updated' => __('activity_logs.events.updated'),
                                    'deleted' => __('activity_logs.events.deleted'),
                                    'restored' => __('activity_logs.events.restored'),
                                    'login' => __('activity_logs.events.login'),
                                    'logout' => __('activity_logs.events.logout'),
                                    'failed_login' => __('activity_logs.events.failed_login'),
                                    'password_changed' => __('activity_logs.events.password_changed'),
                                    'email_verified' => __('activity_logs.events.email_verified'),
                                    'custom' => __('activity_logs.events.custom'),
                                ])
                                ->required()
                                ->default('custom'),
                            
                            TextInput::make('subject_type')
                                ->label(__('activity_logs.subject_type'))
                                ->maxLength(255)
                                ->helperText(__('activity_logs.subject_type_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('subject_id')
                                ->label(__('activity_logs.subject_id'))
                                ->numeric()
                                ->minValue(1)
                                ->helperText(__('activity_logs.subject_id_help')),
                            
                            TextInput::make('causer_type')
                                ->label(__('activity_logs.causer_type'))
                                ->maxLength(255)
                                ->helperText(__('activity_logs.causer_type_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('causer_id')
                                ->label(__('activity_logs.causer_id'))
                                ->numeric()
                                ->minValue(1)
                                ->helperText(__('activity_logs.causer_id_help')),
                            
                            TextInput::make('batch_uuid')
                                ->label(__('activity_logs.batch_uuid'))
                                ->maxLength(36)
                                ->helperText(__('activity_logs.batch_uuid_help')),
                        ]),
                ]),
            
            Section::make(__('activity_logs.properties'))
                ->schema([
                    Textarea::make('properties')
                        ->label(__('activity_logs.properties'))
                        ->rows(5)
                        ->maxLength(1000)
                        ->helperText(__('activity_logs.properties_help'))
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('activity_logs.context_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('ip_address')
                                ->label(__('activity_logs.ip_address'))
                                ->maxLength(45)
                                ->rules(['ip'])
                                ->helperText(__('activity_logs.ip_address_help')),
                            
                            TextInput::make('user_agent')
                                ->label(__('activity_logs.user_agent'))
                                ->maxLength(500)
                                ->helperText(__('activity_logs.user_agent_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('device_type')
                                ->label(__('activity_logs.device_type'))
                                ->maxLength(50)
                                ->helperText(__('activity_logs.device_type_help')),
                            
                            TextInput::make('browser')
                                ->label(__('activity_logs.browser'))
                                ->maxLength(100)
                                ->helperText(__('activity_logs.browser_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('os')
                                ->label(__('activity_logs.os'))
                                ->maxLength(100)
                                ->helperText(__('activity_logs.os_help')),
                            
                            TextInput::make('country')
                                ->label(__('activity_logs.country'))
                                ->maxLength(100)
                                ->helperText(__('activity_logs.country_help')),
                        ]),
                ]),
            
            Section::make(__('activity_logs.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_important')
                                ->label(__('activity_logs.is_important'))
                                ->default(false),
                            
                            Toggle::make('is_system')
                                ->label(__('activity_logs.is_system'))
                                ->default(false),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('severity')
                                ->label(__('activity_logs.severity'))
                                ->maxLength(20)
                                ->helperText(__('activity_logs.severity_help')),
                            
                            TextInput::make('category')
                                ->label(__('activity_logs.category'))
                                ->maxLength(50)
                                ->helperText(__('activity_logs.category_help')),
                        ]),
                    
                    Textarea::make('notes')
                        ->label(__('activity_logs.notes'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
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
                TextColumn::make('description')
                    ->label(__('activity_logs.description'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(100),
                
                TextColumn::make('user.name')
                    ->label(__('activity_logs.user'))
                    ->sortable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('event')
                    ->label(__('activity_logs.event'))
                    ->formatStateUsing(fn (string $state): string => __("activity_logs.events.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'green',
                        'updated' => 'blue',
                        'deleted' => 'red',
                        'restored' => 'purple',
                        'login' => 'success',
                        'logout' => 'warning',
                        'failed_login' => 'danger',
                        'password_changed' => 'info',
                        'email_verified' => 'success',
                        'custom' => 'gray',
                        default => 'gray',
                    }),
                
                TextColumn::make('log_name')
                    ->label(__('activity_logs.log_name'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('subject_type')
                    ->label(__('activity_logs.subject_type'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('subject_id')
                    ->label(__('activity_logs.subject_id'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('causer_type')
                    ->label(__('activity_logs.causer_type'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('causer_id')
                    ->label(__('activity_logs.causer_id'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('ip_address')
                    ->label(__('activity_logs.ip_address'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('device_type')
                    ->label(__('activity_logs.device_type'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('purple')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('browser')
                    ->label(__('activity_logs.browser'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('os')
                    ->label(__('activity_logs.os'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('country')
                    ->label(__('activity_logs.country'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('green')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('severity')
                    ->label(__('activity_logs.severity'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'green',
                        'medium' => 'yellow',
                        'high' => 'orange',
                        'critical' => 'red',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('category')
                    ->label(__('activity_logs.category'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('blue')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_important')
                    ->label(__('activity_logs.is_important'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_system')
                    ->label(__('activity_logs.is_system'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('batch_uuid')
                    ->label(__('activity_logs.batch_uuid'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label(__('activity_logs.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('activity_logs.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label(__('activity_logs.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('event')
                    ->label(__('activity_logs.event'))
                    ->options([
                        'created' => __('activity_logs.events.created'),
                        'updated' => __('activity_logs.events.updated'),
                        'deleted' => __('activity_logs.events.deleted'),
                        'restored' => __('activity_logs.events.restored'),
                        'login' => __('activity_logs.events.login'),
                        'logout' => __('activity_logs.events.logout'),
                        'failed_login' => __('activity_logs.events.failed_login'),
                        'password_changed' => __('activity_logs.events.password_changed'),
                        'email_verified' => __('activity_logs.events.email_verified'),
                        'custom' => __('activity_logs.events.custom'),
                    ]),
                
                SelectFilter::make('log_name')
                    ->label(__('activity_logs.log_name'))
                    ->options(function () {
                        return ActivityLog::distinct('log_name')
                            ->pluck('log_name', 'log_name')
                            ->filter()
                            ->toArray();
                    })
                    ->searchable(),
                
                SelectFilter::make('subject_type')
                    ->label(__('activity_logs.subject_type'))
                    ->options(function () {
                        return ActivityLog::distinct('subject_type')
                            ->pluck('subject_type', 'subject_type')
                            ->filter()
                            ->toArray();
                    })
                    ->searchable(),
                
                SelectFilter::make('causer_type')
                    ->label(__('activity_logs.causer_type'))
                    ->options(function () {
                        return ActivityLog::distinct('causer_type')
                            ->pluck('causer_type', 'causer_type')
                            ->filter()
                            ->toArray();
                    })
                    ->searchable(),
                
                SelectFilter::make('severity')
                    ->label(__('activity_logs.severity'))
                    ->options([
                        'low' => __('activity_logs.severities.low'),
                        'medium' => __('activity_logs.severities.medium'),
                        'high' => __('activity_logs.severities.high'),
                        'critical' => __('activity_logs.severities.critical'),
                    ]),
                
                SelectFilter::make('category')
                    ->label(__('activity_logs.category'))
                    ->options(function () {
                        return ActivityLog::distinct('category')
                            ->pluck('category', 'category')
                            ->filter()
                            ->toArray();
                    })
                    ->searchable(),
                
                TernaryFilter::make('is_important')
                    ->label(__('activity_logs.is_important'))
                    ->boolean()
                    ->trueLabel(__('activity_logs.important_only'))
                    ->falseLabel(__('activity_logs.non_important_only'))
                    ->native(false),
                
                TernaryFilter::make('is_system')
                    ->label(__('activity_logs.is_system'))
                    ->boolean()
                    ->trueLabel(__('activity_logs.system_only'))
                    ->falseLabel(__('activity_logs.user_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                TableAction::make('mark_important')
                    ->label(__('activity_logs.mark_important'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (ActivityLog $record): bool => !$record->is_important)
                    ->action(function (ActivityLog $record): void {
                        $record->update(['is_important' => true]);
                        
                        Notification::make()
                            ->title(__('activity_logs.marked_as_important_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                TableAction::make('unmark_important')
                    ->label(__('activity_logs.unmark_important'))
                    ->icon('heroicon-o-star')
                    ->color('gray')
                    ->visible(fn (ActivityLog $record): bool => $record->is_important)
                    ->action(function (ActivityLog $record): void {
                        $record->update(['is_important' => false]);
                        
                        Notification::make()
                            ->title(__('activity_logs.unmarked_as_important_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                TableAction::make('mark_system')
                    ->label(__('activity_logs.mark_system'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('info')
                    ->visible(fn (ActivityLog $record): bool => !$record->is_system)
                    ->action(function (ActivityLog $record): void {
                        $record->update(['is_system' => true]);
                        
                        Notification::make()
                            ->title(__('activity_logs.marked_as_system_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                TableAction::make('unmark_system')
                    ->label(__('activity_logs.unmark_system'))
                    ->icon('heroicon-o-user')
                    ->color('gray')
                    ->visible(fn (ActivityLog $record): bool => $record->is_system)
                    ->action(function (ActivityLog $record): void {
                        $record->update(['is_system' => false]);
                        
                        Notification::make()
                            ->title(__('activity_logs.unmarked_as_system_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    BulkAction::make('mark_important')
                        ->label(__('activity_logs.mark_important_selected'))
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_important' => true]);
                            
                            Notification::make()
                                ->title(__('activity_logs.bulk_marked_as_important_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('unmark_important')
                        ->label(__('activity_logs.unmark_important_selected'))
                        ->icon('heroicon-o-star')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_important' => false]);
                            
                            Notification::make()
                                ->title(__('activity_logs.bulk_unmarked_as_important_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('mark_system')
                        ->label(__('activity_logs.mark_system_selected'))
                        ->icon('heroicon-o-cog-6-tooth')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_system' => true]);
                            
                            Notification::make()
                                ->title(__('activity_logs.bulk_marked_as_system_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('unmark_system')
                        ->label(__('activity_logs.unmark_system_selected'))
                        ->icon('heroicon-o-user')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_system' => false]);
                            
                            Notification::make()
                                ->title(__('activity_logs.bulk_unmarked_as_system_success'))
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
            'index' => Pages\ListActivityLogs::route('/'),
            'create' => Pages\CreateActivityLog::route('/create'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
            'edit' => Pages\EditActivityLog::route('/{record}/edit'),
        ];
    }
}
