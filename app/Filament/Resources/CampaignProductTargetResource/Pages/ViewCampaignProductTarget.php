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

/**
 * Dedicated view page so marketing specialists can inspect a single target in detail.
 */
final class ViewCampaignProductTarget extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    /**
     * The underlying resource class that powers this page.
     */
    protected static string $resource = CampaignProductTargetResource::class;

    public bool $isTableLoaded = true;

    public function mount($record): void
    {
        parent::mount($record);

        // Preload the table immediately so assertions in tests can operate on the dataset.
        $this->isTableLoaded = true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading(false)
            ->paginated(false)
            ->query(
                // Limit the query to the record currently being viewed while bypassing global scopes.
                CampaignProductTarget::query()
                    ->withoutGlobalScopes()
                    ->whereKey($this->record->getKey())
            )
            ->columns([
                TextColumn::make('campaign.name')
                    ->label(__('campaign_product_targets.columns.campaign')),
                TextColumn::make('target_type')
                    ->label(__('campaign_product_targets.columns.target_type'))
                    ->formatStateUsing(fn (string $state): string => __('campaign_product_targets.types.' . $state)),
                TextColumn::make('target_name')
                    ->label(__('campaign_product_targets.columns.target_name')),
                TextColumn::make('priority')
                    ->label(__('campaign_product_targets.columns.priority')),
                IconColumn::make('is_active')
                    ->label(__('campaign_product_targets.columns.is_active'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('campaign_product_targets.columns.is_featured'))
                    ->boolean(),
            ]);
    }
}
