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
/**
 * SubscriberResource
 *
 * Filament v4 resource for Subscriber management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class SubscriberResource extends Resource
{
    // protected static $navigationGroup = NavigationGroup::Products;
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
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
    public static function getNavigationGroup(): ?string
        return "Marketing";
     * Handle getPluralModelLabel functionality with proper error handling.
    public static function getPluralModelLabel(): string
        return __('subscribers.plural');
     * Handle getModelLabel functionality with proper error handling.
    public static function getModelLabel(): string
        return __('subscribers.single');
     * Configure the Filament form schema with fields and validation.
     * @param Form $schema
     * @return Form
    public static function form(Schema $schema): Schema
    {$state}")),
                TextColumn::make('source')
                    ->label(__('subscribers.source'))
                    ->formatStateUsing(fn(string $state): string => __("subscribers.sources.{$state}"))
                    ->badge()
                    ->color('gray')
                IconColumn::make('is_verified')
                    ->label(__('subscribers.is_verified'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('accepts_marketing')
                    ->label(__('subscribers.accepts_marketing'))
                IconColumn::make('newsletter_subscription')
                    ->label(__('subscribers.newsletter_subscription'))
                TextColumn::make('subscribed_at')
                    ->label(__('subscribers.subscribed_at'))
                    ->dateTime()
                TextColumn::make('created_at')
                    ->label(__('subscribers.created_at'))
                TextColumn::make('updated_at')
                    ->label(__('subscribers.updated_at'))
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
                        'website' => __('subscribers.sources.website'),
                        'admin' => __('subscribers.sources.admin'),
                        'import' => __('subscribers.sources.import'),
                        'api' => __('subscribers.sources.api'),
                        'other' => __('subscribers.sources.other'),
                TernaryFilter::make('is_verified')
                    ->trueLabel(__('subscribers.verified_only'))
                    ->falseLabel(__('subscribers.unverified_only'))
                    ->native(false),
                TernaryFilter::make('accepts_marketing')
                    ->trueLabel(__('subscribers.accepts_marketing_only'))
                    ->falseLabel(__('subscribers.does_not_accept_marketing'))
                Filter::make('subscribed_at')
                    ->form([
                        Forms\Components\DatePicker::make('subscribed_from')
                            ->label(__('subscribers.subscribed_from')),
                        Forms\Components\DatePicker::make('subscribed_until')
                            ->label(__('subscribers.subscribed_until')),
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['subscribed_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('subscribed_at', '>=', $date),
                            )
                                $data['subscribed_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('subscribed_at', '<=', $date),
                            );
                    }),
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
                        $record->update([
                            'status' => 'unsubscribed',
                            'unsubscribed_at' => now(),
                        ]);
                            ->title(__('subscribers.unsubscribed_successfully'))
                Action::make('resubscribe')
                    ->label(__('subscribers.resubscribe'))
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn(Subscriber $record): bool => $record->status === 'unsubscribed')
                            'status' => 'active',
                            'unsubscribed_at' => null,
                            'unsubscribe_reason' => null,
                            ->title(__('subscribers.resubscribed_successfully'))
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
                            $records->each->update([
                                'status' => 'unsubscribed',
                                'unsubscribed_at' => now(),
                            ]);
                                ->title(__('subscribers.bulk_unsubscribed_success'))
                    BulkAction::make('export')
                        ->label(__('subscribers.export_selected'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                            // Export logic here
                                ->title(__('subscribers.exported_successfully'))
                        }),
            ->defaultSort('created_at', 'desc');
     * Get the relations for this resource.
     * @return array
    public static function getRelations(): array
        return [
            //
        ];
     * Get the pages for this resource.
    public static function getPages(): array
            'index' => Pages\ListSubscribers::route('/'),
            'create' => Pages\CreateSubscriber::route('/create'),
            'view' => Pages\ViewSubscriber::route('/{record}'),
            'edit' => Pages\EditSubscriber::route('/{record}/edit'),
}
