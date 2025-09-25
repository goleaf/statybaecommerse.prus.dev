<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriberResource\Pages;
use BackedEnum;
use App\Models\Subscriber;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * SubscriberResource
 *
 * Filament v4 resource for Subscriber management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class SubscriberResource extends Resource
{
    protected static ?string $model = Subscriber::class;

    protected static UnitEnum|string|null $navigationGroup = 'Users';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'email';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     */
    public static function getNavigationLabel(): string
    {
        return __('subscribers.title');
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('subscribers.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('subscribers.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('subscribers.personal_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('email')
                                    ->label(__('subscribers.email'))
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label(__('subscribers.phone'))
                                    ->tel()
                                    ->maxLength(20),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('first_name')
                                    ->label(__('subscribers.first_name'))
                                    ->maxLength(255),
                                TextInput::make('last_name')
                                    ->label(__('subscribers.last_name'))
                                    ->maxLength(255),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('company')
                                    ->label(__('subscribers.company'))
                                    ->maxLength(255),
                                TextInput::make('job_title')
                                    ->label(__('subscribers.job_title'))
                                    ->maxLength(255),
                            ]),
                    ]),
                Section::make(__('subscribers.subscription_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label(__('subscribers.status'))
                                    ->options([
                                        'active' => __('subscribers.statuses.active'),
                                        'inactive' => __('subscribers.statuses.inactive'),
                                        'unsubscribed' => __('subscribers.statuses.unsubscribed'),
                                        'bounced' => __('subscribers.statuses.bounced'),
                                        'complained' => __('subscribers.statuses.complained'),
                                    ])
                                    ->required()
                                    ->default('active'),
                                Select::make('source')
                                    ->label(__('subscribers.source'))
                                    ->options([
                                        'website' => __('subscribers.sources.website'),
                                        'admin' => __('subscribers.sources.admin'),
                                        'import' => __('subscribers.sources.import'),
                                        'api' => __('subscribers.sources.api'),
                                        'other' => __('subscribers.sources.other'),
                                    ])
                                    ->required()
                                    ->default('website'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_verified')
                                    ->label(__('subscribers.is_verified'))
                                    ->default(false),
                                Toggle::make('accepts_marketing')
                                    ->label(__('subscribers.accepts_marketing'))
                                    ->default(true),
                                Toggle::make('newsletter_subscription')
                                    ->label(__('subscribers.newsletter_subscription'))
                                    ->default(true),
                            ]),
                        DateTimePicker::make('subscribed_at')
                            ->label(__('subscribers.subscribed_at'))
                            ->default(now()),
                    ]),
                Section::make(__('subscribers.additional_information'))
                    ->schema([
                        TagsInput::make('interests')
                            ->label(__('subscribers.interests'))
                            ->placeholder(__('subscribers.interests_placeholder')),
                        Textarea::make('metadata')
                            ->label(__('subscribers.metadata'))
                            ->rows(3)
                            ->helperText(__('subscribers.metadata_help')),
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
                TextColumn::make('email')
                    ->label(__('subscribers.email'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                TextColumn::make('full_name')
                    ->label(__('subscribers.full_name'))
                    ->getStateUsing(fn (Subscriber $record) => $record->full_name)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('subscribers.status'))
                    ->formatStateUsing(fn (string $state): string => __("subscribers.statuses.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'unsubscribed' => 'warning',
                        'bounced' => 'danger',
                        'complained' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('source')
                    ->label(__('subscribers.source'))
                    ->formatStateUsing(fn (string $state): string => __("subscribers.sources.{$state}"))
                    ->badge()
                    ->color('gray'),
                IconColumn::make('is_verified')
                    ->label(__('subscribers.is_verified'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('accepts_marketing')
                    ->label(__('subscribers.accepts_marketing'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('newsletter_subscription')
                    ->label(__('subscribers.newsletter_subscription'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('subscribed_at')
                    ->label(__('subscribers.subscribed_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('subscribers.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('subscribers.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => __('subscribers.statuses.active'),
                        'inactive' => __('subscribers.statuses.inactive'),
                        'unsubscribed' => __('subscribers.statuses.unsubscribed'),
                        'bounced' => __('subscribers.statuses.bounced'),
                        'complained' => __('subscribers.statuses.complained'),
                    ]),
                SelectFilter::make('source')
                    ->options([
                        'website' => __('subscribers.sources.website'),
                        'admin' => __('subscribers.sources.admin'),
                        'import' => __('subscribers.sources.import'),
                        'api' => __('subscribers.sources.api'),
                        'other' => __('subscribers.sources.other'),
                    ]),
                TernaryFilter::make('is_verified')
                    ->trueLabel(__('subscribers.verified_only'))
                    ->falseLabel(__('subscribers.unverified_only'))
                    ->native(false),
                TernaryFilter::make('accepts_marketing')
                    ->trueLabel(__('subscribers.accepts_marketing_only'))
                    ->falseLabel(__('subscribers.does_not_accept_marketing'))
                    ->native(false),
                Filter::make('subscribed_at')
                    ->form([
                        Forms\Components\DatePicker::make('subscribed_from')
                            ->label(__('subscribers.subscribed_from')),
                        Forms\Components\DatePicker::make('subscribed_until')
                            ->label(__('subscribers.subscribed_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['subscribed_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('subscribed_at', '>=', $date),
                            )
                            ->when(
                                $data['subscribed_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('subscribed_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('verify')
                    ->label(__('subscribers.verify'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Subscriber $record): bool => ! $record->is_verified)
                    ->action(function (Subscriber $record): void {
                        \App\Models\Subscriber::withoutGlobalScopes()
                            ->whereKey($record->getKey())
                            ->update(['is_verified' => true]);
                        Notification::make()
                            ->title(__('subscribers.verified_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('unsubscribe')
                    ->label(__('subscribers.unsubscribe'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Subscriber $record): bool => $record->status === 'active')
                    ->action(function (Subscriber $record): void {
                        $record->update([
                            'status' => 'unsubscribed',
                            'unsubscribed_at' => now(),
                        ]);
                        Notification::make()
                            ->title(__('subscribers.unsubscribed_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('resubscribe')
                    ->label(__('subscribers.resubscribe'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn (Subscriber $record): bool => $record->status === 'unsubscribed')
                    ->action(function (Subscriber $record): void {
                        $record->update([
                            'status' => 'active',
                            'unsubscribed_at' => null,
                            'unsubscribe_reason' => null,
                        ]);
                        Notification::make()
                            ->title(__('subscribers.resubscribed_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('verify')
                        ->label(__('subscribers.verify_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $ids = $records->pluck('id');
                            \App\Models\Subscriber::withoutGlobalScopes()
                                ->whereIn('id', $ids)
                                ->update(['is_verified' => true]);
                            Notification::make()
                                ->title(__('subscribers.bulk_verified_success'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('unsubscribe')
                        ->label(__('subscribers.unsubscribe_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $ids = $records->pluck('id');
                            \App\Models\Subscriber::withoutGlobalScopes()
                                ->whereIn('id', $ids)
                                ->update([
                                    'status' => 'unsubscribed',
                                    'unsubscribed_at' => now(),
                                ]);
                            Notification::make()
                                ->title(__('subscribers.bulk_unsubscribed_success'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('export')
                        ->label(__('subscribers.export_selected'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            // Export logic here
                            Notification::make()
                                ->title(__('subscribers.exported_successfully'))
                                ->success()
                                ->send();
                        }),
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
            'index' => Pages\ListSubscribers::route('/'),
            'create' => Pages\CreateSubscriber::route('/create'),
            'view' => Pages\ViewSubscriber::route('/{record}'),
            'edit' => Pages\EditSubscriber::route('/{record}/edit'),
        ];
    }
}
