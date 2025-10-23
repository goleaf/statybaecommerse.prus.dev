<?php

declare(strict_types=1);

namespace App\Filament\Resources\MenuItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;

class MenuItemForm
{
    public static function configure(Form $form): Form
    {
        // Build the schema directly on the Form instance so Filament applies validation consistently.
        return $form
            ->schema([
                Select::make('menu_id')
                    ->relationship('menu', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    // Validate the selected menu exists to prevent stale option submissions.
                    ->exists('menus', 'id'),
                Select::make('parent_id')
                    ->relationship('parent', 'id')
                    ->nullable()
                    ->searchable()
                    ->preload()
                    // Keep parent assignments constrained to persisted records on the same menu.
                    ->exists('menu_items', 'id'),
                TextInput::make('label')
                    ->required()
                    // Enforce a sensible label length for navigation rendering.
                    ->maxLength(255),
                TextInput::make('url')
                    ->url()
                    // Guard against overly long external URLs.
                    ->maxLength(255),
                TextInput::make('route_name')
                    // Provide optional internal route wiring without truncation issues.
                    ->maxLength(255),
                Textarea::make('route_params')
                    ->columnSpanFull()
                    ->rules(['nullable', 'json'])
                    ->formatStateUsing(static function ($state): ?string {
                        // Present stored arrays as human-readable JSON for quick adjustments.
                        if ($state === null || $state === '') {
                            return null;
                        }

                        return is_array($state)
                            ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                            : (string) $state;
                    })
                    ->dehydrateStateUsing(static function (?string $state): ?array {
                        // Convert valid JSON payloads back to arrays before persistence so casts stay consistent.
                        if ($state === null || trim($state) === '') {
                            return null;
                        }

                        $decoded = json_decode($state, true);

                        return is_array($decoded) ? $decoded : null;
                    })
                    ->helperText('Provide JSON encoded parameters for named routes.'),
                TextInput::make('icon')
                    // Permit optional Heroicon aliases without overflowing the database column.
                    ->maxLength(100),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    // Default to zero so new links surface predictably without manual ordering.
                    ->default(0),
                Toggle::make('is_visible')
                    // Keep the toggle explicit so hidden links require deliberate action.
                    ->required(),
            ]);
    }
}
