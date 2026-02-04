<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Service;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('quantity')
                    ->numeric()
                    ->default(1)
                    ->required(),
                TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->prefix('$'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('pivot.price')
                    ->label('Price')
                    ->money(),
                TextColumn::make('pivot.quantity')
                    ->label('Quantity'),
                TextColumn::make('pivot.created_at')
                    ->label('Added At')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AttachAction::make()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('quantity')->numeric()->default(1)->required(),
                        TextInput::make('price')->numeric()->required()->prefix('$'),
                    ])
                    ->mutatePivotFormDataUsing(function (array $data): array {
                        return [
                            'quantity' => $data['quantity'],
                            'price'    => $data['price'],
                        ];
                    }),
                Action::make('add_all_services')
                    ->label('Add All Services')
                    ->action(function () {
                        $services = Service::where('is_active', true)->get();
                        foreach ($services as $service) {
                            if (! $this->getOwnerRecord()->services()->where('service_id', $service->id)->exists()) {
                                $this->getOwnerRecord()->services()->attach($service->id, [
                                    'price'    => $service->price,
                                    'quantity' => 1,
                                ]);
                            }
                        }
                    })
                    ->requiresConfirmation()
                    ->color('success')
                    ->icon('heroicon-o-plus-circle'),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
