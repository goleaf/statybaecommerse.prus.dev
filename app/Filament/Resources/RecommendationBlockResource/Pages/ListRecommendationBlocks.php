<?php

declare (strict_types=1);
namespace App\Filament\Resources\RecommendationBlockResource\Pages;

use App\Filament\Resources\RecommendationBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
/**
 * ListRecommendationBlocks
 * 
 * Filament v4 resource for ListRecommendationBlocks management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ListRecommendationBlocks extends ListRecords
{
    protected static string $resource = RecommendationBlockResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}