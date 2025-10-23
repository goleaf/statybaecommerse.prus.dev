<?php

declare(strict_types=1);

namespace App\Filament\Resources\MenuResource\RelationManagers;


use Filament\Schemas\Schema;
use App\Models\MenuItem;
use App\Models\Scopes\VisibleScope;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class MenuItemsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'allItems';

    public function form(Schema $schema): Schema   
    {
        return $schema->schema([
            Grid::make(2)
                ->schema([
                    TextInput::make('label')
                        ->label(__('menus.item_label'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('sort_order')
                        ->label(__('menus.item_sort_order'))
                        ->numeric()
                        ->default(0),
                    TextInput::make('url')
                        ->label(__('menus.item_url'))
                        ->maxLength(255)
                        ->url()
                        ->columnSpanFull(),
                    TextInput::make('route_name')
                        ->label(__('menus.item_route_name'))
                        ->maxLength(255)
                        ->helperText(__('menus.item_route_name_help'))
                        ->columnSpanFull(),
                    Select::make('parent_id')
                        ->label(__('admin.menu_items.parent'))
                        ->options(fn (): array => $this->getParentOptions())
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->columnSpanFull(),
                    Toggle::make('is_visible')
                        ->label(__('menus.item_is_visible'))
                        ->default(true)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public function table(Table $table): Table   
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label(__('menus.item_label'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.label')
                    ->label(__('admin.menu_items.parent'))
                    ->toggleable()
                    ->limit(30),
                TextColumn::make('sort_order')
                    ->label(__('menus.item_sort_order'))
                    ->sortable(),
                IconColumn::make('is_visible')
                    ->label(__('menus.item_is_visible'))
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                RelationManagerRepeaterAction::make()
                    ->label('Quick edit ' . $this->getPluralModelLabel())
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit ' . $this->getPluralModelLabel())
                    ->modalWidth('5xl')
                    ->configureRepeater(function (Repeater $repeater): Repeater {
                        // Provide a quick-edit modal for managing records inline.
                        return $repeater->schema($this->getQuickEditSchema());
                    }),
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->withoutGlobalScopes([VisibleScope::class])
            ->orderBy('sort_order');
    }

    /**
     * @return array<int|string, string>
     */
    private function getParentOptions(): array
    {
        /** @var Builder $query */
        $query = MenuItem::query()
            ->withoutGlobalScopes([VisibleScope::class])
            ->where('menu_id', $this->ownerRecord->getKey())
            ->whereNull('parent_id')
            ->orderBy('label');

        if ($this->record) {
            $query->whereKeyNot($this->record->getKey());
        }

        return $query->pluck('label', 'id')->all();
    }
}