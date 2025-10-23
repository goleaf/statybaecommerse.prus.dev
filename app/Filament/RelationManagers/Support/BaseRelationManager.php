<?php

declare(strict_types=1);

namespace App\Filament\RelationManagers\Support;

use App\Filament\Tables\Concerns\ConfiguresToggleableTableLayout;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;

abstract class BaseRelationManager extends RelationManager
{
    use ConfiguresToggleableTableLayout;
    use HasToggleableTable;

    public function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        $table = parent::table($table);

        return $this->applyToggleableTableLayout($table);
    }

    /**
     * Build a schema for quick-edit repeaters that mirrors the relation form.
     *
     * @return array<int, Forms\Components\Component>
     */
    protected function getQuickEditSchema(): array
    {
        $form = $this->form(app(Form::class));

        $components = $form instanceof Form ? $form->getComponents() : $form;

        return array_merge([
            Forms\Components\Hidden::make('id'),
        ], $components);
    }
}
