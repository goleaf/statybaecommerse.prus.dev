<?php

declare (strict_types=1);
namespace App\Filament\Resources\RecommendationConfigResourceSimple\Pages;

use App\Filament\Resources\RecommendationConfigResourceSimple;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
/**
 * ListRecommendationConfigResourceSimples
 * 
 * Filament v4 resource for ListRecommendationConfigResourceSimples management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ListRecommendationConfigResourceSimples extends ListRecords
{
    protected static string $resource = RecommendationConfigResourceSimple::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}