<?php

declare(strict_types=1);

namespace Filament\Forms {
    if (! class_exists(Form::class) && class_exists(\Filament\Schemas\Schema::class)) {
        class_alias(\Filament\Schemas\Schema::class, Form::class);
    }

    if (! class_exists(Get::class) && class_exists(\Filament\Schemas\Components\Utilities\Get::class)) {
        class_alias(\Filament\Schemas\Components\Utilities\Get::class, Get::class);
    }

    if (! class_exists(Set::class) && class_exists(\Filament\Schemas\Components\Utilities\Set::class)) {
        class_alias(\Filament\Schemas\Components\Utilities\Set::class, Set::class);
    }
}

namespace Filament\Forms\Components {
    if (! class_exists(Section::class) && class_exists(\Filament\Schemas\Components\Section::class)) {
        class_alias(\Filament\Schemas\Components\Section::class, Section::class);
    }

    if (! class_exists(Grid::class) && class_exists(\Filament\Schemas\Components\Grid::class)) {
        class_alias(\Filament\Schemas\Components\Grid::class, Grid::class);
    }

    if (! class_exists(Combobox::class) && class_exists(\App\Support\FilamentCompat\Combobox::class)) {
        class_alias(\App\Support\FilamentCompat\Combobox::class, Combobox::class);
    }
}

namespace Filament\Tables {
    if (! class_exists(Table::class) && class_exists(\Filament\Resources\Table::class)) {
        class_alias(\Filament\Resources\Table::class, Table::class);
    }
}

namespace Filament\Infolists {
    if (! class_exists(Infolist::class) && class_exists(\Filament\Schemas\Schema::class)) {
        class_alias(\Filament\Schemas\Schema::class, Infolist::class);
    }
}

namespace Filament\Infolists\Components {
    if (! class_exists(Section::class) && class_exists(\Filament\Schemas\Components\Section::class)) {
        class_alias(\Filament\Schemas\Components\Section::class, Section::class);
    }
}

namespace Asmit\ResizedColumn {
    if (! trait_exists(HasResizableColumn::class) && trait_exists(\App\Support\FilamentCompat\HasResizableColumn::class)) {
        class_alias(\App\Support\FilamentCompat\HasResizableColumn::class, HasResizableColumn::class);
    }
}

namespace Filament\Tables\Actions {
    $aliases = [
        'Action' => \Filament\Actions\Action::class,
        'BulkAction' => \Filament\Actions\BulkAction::class,
        'BulkActionGroup' => \Filament\Actions\BulkActionGroup::class,
        'DeleteAction' => \Filament\Actions\DeleteAction::class,
        'DeleteBulkAction' => \Filament\Actions\DeleteBulkAction::class,
        'EditAction' => \Filament\Actions\EditAction::class,
    ];

    foreach ($aliases as $alias => $class) {
        if (! class_exists($alias) && class_exists($class)) {
            class_alias($class, $alias);
        }
    }
}
