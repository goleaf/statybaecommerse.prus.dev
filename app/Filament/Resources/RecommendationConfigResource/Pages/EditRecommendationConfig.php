<?php

declare (strict_types=1);
namespace App\Filament\Resources\RecommendationConfigResource\Pages;

use App\Filament\Resources\RecommendationConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
/**
 * EditRecommendationConfig
 * 
 * Filament v4 resource for EditRecommendationConfig management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class EditRecommendationConfig extends EditRecord
{
    protected static string $resource = RecommendationConfigResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}