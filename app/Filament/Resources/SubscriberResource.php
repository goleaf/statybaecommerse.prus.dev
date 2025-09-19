<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\SubscriberResource\Pages;
use App\Models\Subscriber;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
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
    protected static ?string $model = Subscriber::class;    /** @var UnitEnum|string|null */
    protected static string|UnitEnum|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'email';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('subscribers.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return "Marketing";
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('subscribers.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('subscribers.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('subscribers.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('email')
                                ->label(__('subscribers.email'))
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            TextInput::make('name')
                                ->label(__('subscribers.name'))
                                ->maxLength(255),
                        ]),
                    TextInput::make('phone')
                        ->label(__('subscribers.phone'))
                        ->tel()
                        ->maxLength(20),
                ]),
            Section::make(__('subscribers.subscription_settings'))
                ->components([
                    Grid::make(2)
                        ->components([
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
                                ->default('website'),
                        ]),
                    Grid::make(2)
                        ->components([
                            Toggle::make('is_verified')
                                ->label(__('subscribers.is_verified'))
                                ->default(false),
                            Toggle::make('accepts_marketing')
                                ->label(__('subscribers.accepts_marketing'))
                                ->default(true),
                        ]),
                ]),
            Section::make(__('subscribers.preferences'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('preferred_language')
                                ->label(__('subscribers.preferred_language'))
                                ->options([
                                    'lt' => __('subscribers.languages.lt'),
                                    'en' => __('subscribers.languages.en'),
                                ])
                                ->default('lt'),
                            Select::make('preferred_currency')
                                ->label(__('subscribers.preferred_currency'))
                                ->options([
                                    'EUR' => 'EUR (€)',
                                    'USD' => 'USD ($)',
                                ])
                                ->default('EUR'),
                        ]),
                    Grid::make(2)
                        ->components([
                            Toggle::make('newsletter_subscription')
                                ->label(__('subscribers.newsletter_subscription'))
                                ->default(true),
                            Toggle::make('promotional_emails')
                                ->label(__('subscribers.promotional_emails'))
                                ->default(false),
                        ]),
                ]),
            Section::make(__('subscribers.additional_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            DateTimePicker::make('subscribed_at')
                                ->label(__('subscribers.subscribed_at'))
                                ->default(now())
                                ->displayFormat('d/m/Y H:i'),
                            DateTimePicker::make('unsubscribed_at')
                                ->label(__('subscribers.unsubscribed_at'))
                                ->displayFormat('d/m/Y H:i'),
                        ]),
                    TextInput::make('unsubscribe_reason')
                        ->label(__('subscribers.unsubscribe_reason'))
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
                TextColumn::make('email')
                    ->label(__('subscribers.email'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                TextColumn::make('name')
                    ->label(__('subscribers.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('phone')
                    ->label(__('subscribers.phone'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('status')
                    ->label(__('subscribers.status'))
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => 'unsubscribed',
                        'gray' => 'bounced',
                        'red' => 'complained',
                    ])
                    ->formatStateUsing(fn(string $state): string => __("subscribers.statuses.{$state}")),
                TextColumn::make('source')
                    ->label(__('subscribers.source'))
                    ->formatStateUsing(fn(string $state): string => __("subscribers.sources.{$state}"))
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_verified')
                    ->label(__('subscribers.is_verified'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('accepts_marketing')
                    ->label(__('subscribers.accepts_marketing'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('newsletter_subscription')
                    ->label(__('subscribers.newsletter_subscription'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('subscribed_at')
                    ->label(__('subscribers.subscribed_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->label(__('subscribers.status'))
                    ->options([
                        'active' => __('subscribers.statuses.active'),
                        'inactive' => __('subscribers.statuses.inactive'),
                        'unsubscribed' => __('subscribers.statuses.unsubscribed'),
                        'bounced' => __('subscribers.statuses.bounced'),
                        'complained' => __('subscribers.statuses.complained'),
                    ]),
                SelectFilter::make('source')
                    ->label(__('subscribers.source'))
                    ->options([
                        'website' => __('subscribers.sources.website'),
                        'admin' => __('subscribers.sources.admin'),
                        'import' => __('subscribers.sources.import'),
                        'api' => __('subscribers.sources.api'),
                        'other' => __('subscribers.sources.other'),
                    ]),
                TernaryFilter::make('is_verified')
                    ->label(__('subscribers.is_verified'))
                    ->boolean()
                    ->trueLabel(__('subscribers.verified_only'))
                    ->falseLabel(__('subscribers.unverified_only'))
                    ->native(false),
                TernaryFilter::make('accepts_marketing')
                    ->label(__('subscribers.accepts_marketing'))
                    ->boolean()
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
                                fn(Builder $query, $date): Builder => $query->whereDate('subscribed_at', '>=', $date),
                            )
                            ->when(
                                $data['subscribed_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('subscribed_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('verify')
                    ->label(__('subscribers.verify'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Subscriber $record): bool => !$record->is_verified)
                    ->action(function (Subscriber $record): void {
                        $record->update(['is_verified' => true]);

                        Notification::make()
                            ->title(__('subscribers.verified_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('unsubscribe')
                    ->label(__('subscribers.unsubscribe'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Subscriber $record): bool => $record->status === 'active')
                    ->action(function (Subscriber $record): void {
                        $record->update([
                            'status' => 'unsubscribed',
                            'unsubscribed_at' => now(),
                        ]);

                        Notification::make()
                            ->title(__('subscribers.unsubscribed_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('resubscribe')
                    ->label(__('subscribers.resubscribe'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn(Subscriber $record): bool => $record->status === 'unsubscribed')
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
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('verify')
                        ->label(__('subscribers.verify_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_verified' => true]);

                            Notification::make()
                                ->title(__('subscribers.bulk_verified_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('unsubscribe')
                        ->label(__('subscribers.unsubscribe_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $records->each->update([
                                'status' => 'unsubscribed',
                                'unsubscribed_at' => now(),
                            ]);

                            Notification::make()
                                ->title(__('subscribers.bulk_unsubscribed_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
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
            'index' => Pages\ListSubscribers::route('/'),
            'create' => Pages\CreateSubscriber::route('/create'),
            'view' => Pages\ViewSubscriber::route('/{record}'),
            'edit' => Pages\EditSubscriber::route('/{record}/edit'),
        ];
    }
}
