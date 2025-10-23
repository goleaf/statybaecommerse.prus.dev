<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProductInteractionResource\Pages;

use App\Filament\Resources\UserProductInteractionResource;
use App\Filament\Tables\Concerns\ConfiguresToggleableTableLayout;
use App\Models\UserProductInteraction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;

final class ViewUserProductInteraction extends ViewRecord implements HasTable
{
    use ConfiguresToggleableTableLayout;
    use HasToggleableTable;
    use InteractsWithTable;

    protected static string $resource = UserProductInteractionResource::class;

    public function mount($record): void
    {
        parent::mount($record);
        $this->isTableLoaded = true;
    }

    public function table(Table $table): Table   
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        $table = $table
            ->deferLoading(false)
            ->query(UserProductInteraction::query()->whereKey($this->record->getKey()))
            ->columns([
                TextColumn::make('id'),
            ]);

        return $this->applyToggleableTableLayout($table);
    }
}