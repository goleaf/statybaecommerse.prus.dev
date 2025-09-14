<?php

declare (strict_types=1);
namespace App\Filament\Resources\RecommendationConfigResource\Pages;

use App\Filament\Resources\RecommendationConfigResource;
use Filament\Resources\Pages\CreateRecord;
/**
 * CreateRecommendationConfig
 * 
 * Filament v4 resource for CreateRecommendationConfig management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CreateRecommendationConfig extends CreateRecord
{
    protected static string $resource = RecommendationConfigResource::class;
}