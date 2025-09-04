<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AnalyticsEventResource\Pages;
use App\Models\AnalyticsEvent;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

final class AnalyticsEventResource extends Resource
{
    protected static ?string $model = AnalyticsEvent::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Analytics Events';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Event Information')
                    ->components([
                        Forms\Components\TextInput::make('event_type')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('session_id')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('url')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('referrer')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('ip_address')
                            ->ip()
                            ->maxLength(45),
                        Forms\Components\TextInput::make('country_code')
                            ->maxLength(2),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->required(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Event Properties')
                    ->components([
                        Forms\Components\KeyValue::make('properties')
                            ->keyLabel('Property')
                            ->valueLabel('Value'),
                    ])
                    ->collapsible(),
                Forms\Components\Section::make('User Agent')
                    ->components([
                        Forms\Components\Textarea::make('user_agent')
                            ->rows(3),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event_type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'page_view' => 'info',
                        'product_view' => 'primary',
                        'add_to_cart' => 'success',
                        'purchase' => 'success',
                        'search' => 'warning',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('Anonymous')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('session_id')
                    ->label('Session')
                    ->limit(10)
                    ->tooltip(fn($record) => $record->session_id),
                Tables\Columns\TextColumn::make('url')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->url),
                Tables\Columns\TextColumn::make('country_code')
                    ->label('Country')
                    ->badge()
                    ->placeholder('Unknown'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')
                    ->options([
                        'page_view' => 'Page View',
                        'product_view' => 'Product View',
                        'add_to_cart' => 'Add to Cart',
                        'remove_from_cart' => 'Remove from Cart',
                        'purchase' => 'Purchase',
                        'search' => 'Search',
                        'user_register' => 'User Register',
                        'user_login' => 'User Login',
                    ]),
                Tables\Filters\Filter::make('has_user')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('user_id'))
                    ->label('Registered Users Only'),
                Tables\Filters\Filter::make('anonymous_only')
                    ->query(fn(Builder $query): Builder => $query->whereNull('user_id'))
                    ->label('Anonymous Users Only'),
                Tables\Filters\Filter::make('today')
                    ->query(fn(Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->label('Today'),
                Tables\Filters\Filter::make('this_week')
                    ->query(fn(Builder $query): Builder => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                    ->label('This Week'),
                Tables\Filters\Filter::make('this_month')
                    ->query(fn(Builder $query): Builder => $query->whereMonth('created_at', now()->month))
                    ->label('This Month'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('cleanup_old_events')
                        ->label('Cleanup Old Events')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->action(function () {
                            AnalyticsEvent::where('created_at', '<', now()->subMonths(3))->delete();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Cleanup Old Analytics Events')
                        ->modalDescription('This will delete all analytics events older than 3 months. This action cannot be undone.')
                        ->modalSubmitActionLabel('Cleanup Old Events'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnalyticsEvents::route('/'),
            'view' => Pages\ViewAnalyticsEvent::route('/{record}'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['event_type', 'user.name', 'url'];
    }

    public static function canCreate(): bool
    {
        return false; // Analytics events are created programmatically
    }

    public static function canEdit($record): bool
    {
        return false; // Analytics events should not be edited
    }
}
