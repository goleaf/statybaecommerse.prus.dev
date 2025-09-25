<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProductInteractionResource\Pages;

use App\Filament\Resources\UserProductInteractionResource;
use App\Models\UserProductInteraction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

final class ViewUserProductInteraction extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = UserProductInteractionResource::class;

    public function mount($record): void
    {
        parent::mount($record);
        $this->isTableLoaded = true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading(false)
            ->query(UserProductInteraction::query()->whereKey($this->record->getKey()))
            ->columns([
                TextColumn::make('id'),
            ]);
    }
}
