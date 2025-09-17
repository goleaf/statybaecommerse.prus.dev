<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use App\Models\CustomerGroup;
use App\Enums\NavigationGroup;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
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
 * CampaignResource
 * 
 * Filament v4 resource for Campaign management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;
    
    /** @var UnitEnum|string|null */
    protected static UnitEnum|string|null  = NavigationGroup::Marketing;
    
    protected static ?int $navigationSort = 7;
    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('campaigns.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Marketing->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('campaigns.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('campaigns.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Form $form): Form{
        return $form->schema([
            Section::make(__('campaigns.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('campaigns.name'))
                                ->required()
                                ->maxLength(255),
                            
                            TextInput::make('code')
                                ->label(__('campaigns.code'))
                                ->required()
                                ->maxLength(50)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    
                    Textarea::make('description')
                        ->label(__('campaigns.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('campaigns.campaign_settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('type')
                                ->label(__('campaigns.type'))
                                ->options([
                                    'email' => __('campaigns.types.email'),
                                    'sms' => __('campaigns.types.sms'),
                                    'push' => __('campaigns.types.push'),
                                    'banner' => __('campaigns.types.banner'),
                                    'popup' => __('campaigns.types.popup'),
                                    'social' => __('campaigns.types.social'),
                                    'affiliate' => __('campaigns.types.affiliate'),
                                ])
                                ->required()
                                ->default('email')
                                ->live(),
                            
                            Select::make('status')
                                ->label(__('campaigns.status'))
                                ->options([
                                    'draft' => __('campaigns.statuses.draft'),
                                    'scheduled' => __('campaigns.statuses.scheduled'),
                                    'running' => __('campaigns.statuses.running'),
                                    'paused' => __('campaigns.statuses.paused'),
                                    'completed' => __('campaigns.statuses.completed'),
                                    'cancelled' => __('campaigns.statuses.cancelled'),
                                ])
                                ->required()
                                ->default('draft'),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('budget')
                                ->label(__('campaigns.budget'))
                                ->numeric()
                                ->prefix('€')
                                ->step(0.01)
                                ->minValue(0)
                                ->helperText(__('campaigns.budget_help')),
                            
                            TextInput::make('target_audience')
                                ->label(__('campaigns.target_audience'))
                                ->maxLength(255)
                                ->helperText(__('campaigns.target_audience_help')),
                        ]),
                ]),
            
            Section::make(__('campaigns.scheduling'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('start_date')
                                ->label(__('campaigns.start_date'))
                                ->default(now())
                                ->displayFormat('d/m/Y H:i'),
                            
                            DateTimePicker::make('end_date')
                                ->label(__('campaigns.end_date'))
                                ->displayFormat('d/m/Y H:i'),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('scheduled_at')
                                ->label(__('campaigns.scheduled_at'))
                                ->displayFormat('d/m/Y H:i'),
                            
                            Toggle::make('is_recurring')
                                ->label(__('campaigns.is_recurring'))
                                ->default(false),
                        ]),
                ]),
            
            Section::make(__('campaigns.targeting'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('customer_groups')
                                ->label(__('campaigns.customer_groups'))
                                ->relationship('customerGroups', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    Textarea::make('description')
                                        ->maxLength(500),
                                ]),
                            
                            Toggle::make('target_new_customers')
                                ->label(__('campaigns.target_new_customers'))
                                ->default(false),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            Toggle::make('target_existing_customers')
                                ->label(__('campaigns.target_existing_customers'))
                                ->default(true),
                            
                            Toggle::make('target_inactive_customers')
                                ->label(__('campaigns.target_inactive_customers'))
                                ->default(false),
                        ]),
                ]),
            
            Section::make(__('campaigns.content'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('subject')
                                ->label(__('campaigns.subject'))
                                ->maxLength(255)
                                ->visible(fn (Forms\Get $get): bool => $get('type') === 'email'),
                            
                            TextInput::make('sender_name')
                                ->label(__('campaigns.sender_name'))
                                ->maxLength(255)
                                ->visible(fn (Forms\Get $get): bool => $get('type') === 'email'),
                        ]),
                    
                    Textarea::make('content')
                        ->label(__('campaigns.content'))
                        ->rows(5)
                        ->maxLength(1000)
                        ->columnSpanFull(),
                    
                    TextInput::make('cta_text')
                        ->label(__('campaigns.cta_text'))
                        ->maxLength(100)
                        ->helperText(__('campaigns.cta_text_help')),
                    
                    TextInput::make('cta_url')
                        ->label(__('campaigns.cta_url'))
                        ->url()
                        ->maxLength(255)
                        ->helperText(__('campaigns.cta_url_help')),
                ]),
            
            Section::make(__('campaigns.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('campaigns.is_active'))
                                ->default(true),
                            
                            Toggle::make('is_automatic')
                                ->label(__('campaigns.is_automatic'))
                                ->default(false),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('priority')
                                ->label(__('campaigns.priority'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText(__('campaigns.priority_help')),
                            
                            Toggle::make('track_clicks')
                                ->label(__('campaigns.track_clicks'))
                                ->default(true),
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
                    ->label(__('campaigns.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('code')
                    ->label(__('campaigns.code'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                
                TextColumn::make('type')
                    ->label(__('campaigns.type'))
                    ->formatStateUsing(fn (string $state): string => __("campaigns.types.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'email' => 'blue',
                        'sms' => 'green',
                        'push' => 'purple',
                        'banner' => 'orange',
                        'popup' => 'pink',
                        'social' => 'indigo',
                        'affiliate' => 'teal',
                        default => 'gray',
                    }),
                
                TextColumn::make('status')
                    ->label(__('campaigns.status'))
                    ->formatStateUsing(fn (string $state): string => __("campaigns.statuses.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'scheduled' => 'warning',
                        'running' => 'success',
                        'paused' => 'info',
                        'completed' => 'blue',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                
                TextColumn::make('budget')
                    ->label(__('campaigns.budget'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('target_audience')
                    ->label(__('campaigns.target_audience'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('customer_groups_count')
                    ->label(__('campaigns.customer_groups_count'))
                    ->counts('customerGroups')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('start_date')
                    ->label(__('campaigns.start_date'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('end_date')
                    ->label(__('campaigns.end_date'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('scheduled_at')
                    ->label(__('campaigns.scheduled_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_active')
                    ->label(__('campaigns.is_active'))
                    ->boolean()
                    ->sortable(),
                
                IconColumn::make('is_recurring')
                    ->label(__('campaigns.is_recurring'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_automatic')
                    ->label(__('campaigns.is_automatic'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('track_clicks')
                    ->label(__('campaigns.track_clicks'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('priority')
                    ->label(__('campaigns.priority'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label(__('campaigns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('campaigns.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('campaigns.type'))
                    ->options([
                        'email' => __('campaigns.types.email'),
                        'sms' => __('campaigns.types.sms'),
                        'push' => __('campaigns.types.push'),
                        'banner' => __('campaigns.types.banner'),
                        'popup' => __('campaigns.types.popup'),
                        'social' => __('campaigns.types.social'),
                        'affiliate' => __('campaigns.types.affiliate'),
                    ]),
                
                SelectFilter::make('status')
                    ->label(__('campaigns.status'))
                    ->options([
                        'draft' => __('campaigns.statuses.draft'),
                        'scheduled' => __('campaigns.statuses.scheduled'),
                        'running' => __('campaigns.statuses.running'),
                        'paused' => __('campaigns.statuses.paused'),
                        'completed' => __('campaigns.statuses.completed'),
                        'cancelled' => __('campaigns.statuses.cancelled'),
                    ]),
                
                TernaryFilter::make('is_active')
                    ->label(__('campaigns.is_active'))
                    ->boolean()
                    ->trueLabel(__('campaigns.active_only'))
                    ->falseLabel(__('campaigns.inactive_only'))
                    ->native(false),
                
                TernaryFilter::make('is_recurring')
                    ->label(__('campaigns.is_recurring'))
                    ->boolean()
                    ->trueLabel(__('campaigns.recurring_only'))
                    ->falseLabel(__('campaigns.non_recurring_only'))
                    ->native(false),
                
                TernaryFilter::make('is_automatic')
                    ->label(__('campaigns.is_automatic'))
                    ->boolean()
                    ->trueLabel(__('campaigns.automatic_only'))
                    ->falseLabel(__('campaigns.manual_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                TableAction::make('start')
                    ->label(__('campaigns.start'))
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (Campaign $record): bool => in_array($record->status, ['draft', 'scheduled', 'paused']))
                    ->action(function (Campaign $record): void {
                        $record->update([
                            'status' => 'running',
                            'start_date' => now(),
                        ]);
                        
                        Notification::make()
                            ->title(__('campaigns.started_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                TableAction::make('pause')
                    ->label(__('campaigns.pause'))
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (Campaign $record): bool => $record->status === 'running')
                    ->action(function (Campaign $record): void {
                        $record->update(['status' => 'paused']);
                        
                        Notification::make()
                            ->title(__('campaigns.paused_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                TableAction::make('complete')
                    ->label(__('campaigns.complete'))
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn (Campaign $record): bool => $record->status === 'running')
                    ->action(function (Campaign $record): void {
                        $record->update([
                            'status' => 'completed',
                            'end_date' => now(),
                        ]);
                        
                        Notification::make()
                            ->title(__('campaigns.completed_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                TableAction::make('cancel')
                    ->label(__('campaigns.cancel'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Campaign $record): bool => in_array($record->status, ['draft', 'scheduled', 'running', 'paused']))
                    ->action(function (Campaign $record): void {
                        $record->update(['status' => 'cancelled']);
                        
                        Notification::make()
                            ->title(__('campaigns.cancelled_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                TableAction::make('toggle_active')
                    ->label(fn (Campaign $record): string => $record->is_active ? __('campaigns.deactivate') : __('campaigns.activate'))
                    ->icon(fn (Campaign $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Campaign $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Campaign $record): void {
                        $record->update(['is_active' => !$record->is_active]);
                        
                        Notification::make()
                            ->title($record->is_active ? __('campaigns.activated_successfully') : __('campaigns.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    BulkAction::make('start')
                        ->label(__('campaigns.start_selected'))
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update([
                                'status' => 'running',
                                'start_date' => now(),
                            ]);
                            
                            Notification::make()
                                ->title(__('campaigns.bulk_started_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('pause')
                        ->label(__('campaigns.pause_selected'))
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => 'paused']);
                            
                            Notification::make()
                                ->title(__('campaigns.bulk_paused_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('complete')
                        ->label(__('campaigns.complete_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $records->each->update([
                                'status' => 'completed',
                                'end_date' => now(),
                            ]);
                            
                            Notification::make()
                                ->title(__('campaigns.bulk_completed_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('cancel')
                        ->label(__('campaigns.cancel_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => 'cancelled']);
                            
                            Notification::make()
                                ->title(__('campaigns.bulk_cancelled_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('activate')
                        ->label(__('campaigns.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            
                            Notification::make()
                                ->title(__('campaigns.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('deactivate')
                        ->label(__('campaigns.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            
                            Notification::make()
                                ->title(__('campaigns.bulk_deactivated_success'))
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
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'view' => Pages\ViewCampaign::route('/{record}'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
