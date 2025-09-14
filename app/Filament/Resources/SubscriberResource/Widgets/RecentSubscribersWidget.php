<?php

declare (strict_types=1);
namespace App\Filament\Resources\SubscriberResource\Widgets;

use App\Models\Subscriber;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
/**
 * RecentSubscribersWidget
 * 
 * Filament v4 resource for RecentSubscribersWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property int|string|array $columnSpan
 * @property string|null $heading
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class RecentSubscribersWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Recent Subscribers';
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->query(Subscriber::query()->recent(7)->latest('subscribed_at')->limit(10))->columns([Tables\Columns\TextColumn::make('email')->searchable()->sortable()->weight('bold'), Tables\Columns\TextColumn::make('full_name')->label('Full Name')->getStateUsing(fn(Subscriber $record): string => $record->full_name)->searchable(['first_name', 'last_name']), Tables\Columns\TextColumn::make('source')->badge()->colors(['primary' => 'website', 'secondary' => 'admin', 'success' => 'import', 'warning' => 'api', 'info' => 'social']), Tables\Columns\TextColumn::make('subscribed_at')->dateTime()->sortable()->since(), Tables\Columns\IconColumn::make('is_active')->label('Active')->getStateUsing(fn(Subscriber $record): bool => $record->is_active)->boolean()])->actions([Tables\Actions\Action::make('view')->label('View')->icon('heroicon-o-eye')->url(fn(Subscriber $record): string => route('filament.admin.resources.subscribers.edit', $record))])->poll('30s');
    }
}