<?php declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $recordTitleAttribute = 'number';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.order_information'))
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->label(__('admin.order_number'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->label(__('admin.status'))
                            ->options([
                                'pending' => __('admin.pending'),
                                'processing' => __('admin.processing'),
                                'shipped' => __('admin.shipped'),
                                'delivered' => __('admin.delivered'),
                                'cancelled' => __('admin.cancelled'),
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('total')
                            ->label(__('admin.total'))
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label(__('admin.notes'))
                            ->rows(3),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('number')
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label(__('admin.order_number'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('admin.total'))
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label(__('admin.items'))
                    ->counts('items'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.order_date'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.status'))
                    ->options([
                        'pending' => __('admin.pending'),
                        'processing' => __('admin.processing'),
                        'shipped' => __('admin.shipped'),
                        'delivered' => __('admin.delivered'),
                        'cancelled' => __('admin.cancelled'),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('admin.create_order')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
