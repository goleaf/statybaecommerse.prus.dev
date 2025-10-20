<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignProductTargetResource\Pages;

use App\Filament\Resources\CampaignProductTargetResource;
use App\Models\CampaignProductTarget;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

final class ViewCampaignProductTarget extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = CampaignProductTargetResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        $this->isTableLoaded = true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading(false)
            ->query(CampaignProductTarget::query()->whereKey($this->record->getKey()))
            ->columns([
                TextColumn::make('id')
                    ->label(__('ID')),
                TextColumn::make('campaign.name')
                    ->label(__('Campaign')),
                TextColumn::make('target_type')
                    ->label(__('Target type')),
                TextColumn::make('target_name')
                    ->label(__('Target')),
                TextColumn::make('priority')
                    ->label(__('Priority')),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('Featured'))
                    ->boolean(),
            ]);
    }
}
