<?php

declare (strict_types=1);
namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;
/**
 * CreatePost
 * 
 * Filament v4 resource for CreatePost management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;
}