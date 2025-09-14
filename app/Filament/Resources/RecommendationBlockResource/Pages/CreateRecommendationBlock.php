<?php

declare (strict_types=1);
namespace App\Filament\Resources\RecommendationBlockResource\Pages;

use App\Filament\Resources\RecommendationBlockResource;
use Filament\Resources\Pages\CreateRecord;
/**
 * CreateRecommendationBlock
 * 
 * Filament v4 resource for CreateRecommendationBlock management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CreateRecommendationBlock extends CreateRecord
{
    protected static string $resource = RecommendationBlockResource::class;
}