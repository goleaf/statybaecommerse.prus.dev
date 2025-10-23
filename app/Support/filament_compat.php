<?php

declare(strict_types=1);

namespace {
    if (! class_exists(\Filament\Forms\Form::class) && class_exists(\Filament\Schemas\Schema::class)) {
        class_alias(\Filament\Schemas\Schema::class, \Filament\Forms\Form::class);
    }

    if (! class_exists(\Filament\Tables\Table::class) && class_exists(\Filament\Resources\Table::class)) {
        class_alias(\Filament\Resources\Table::class, \Filament\Tables\Table::class);
    }
}

namespace Filament\Forms\Components {

    if (! class_exists(Combobox::class) && class_exists(Select::class)) {
        class Combobox extends Select
        {
            public static function make(?string $name = null): static
            {
                return parent::make($name);
            }

            public function boxSearchs(bool $condition = true): static
            {
                return $this;
            }

            public function optionsLabel(?string $label): static
            {
                return $this;
            }

            public function selectedLabel(?string $label): static
            {
                return $this;
            }

            public function height(string|int $value): static
            {
                return $this;
            }
        }
    }
}

namespace Asmit\ResizedColumn {
    if (! trait_exists(HasResizableColumn::class)) {
        trait HasResizableColumn
        {
            /**
             * Provide a minimal stub implementation for the resizable column concern when the vendor package is unavailable.
             *
             * @return array<int, string>
             */
            protected function getResizableColumns(): array
            {
                return [];
            }
        }
    }
}

namespace {
    if (! class_exists(\Filament\Infolists\Infolist::class) && class_exists(\Filament\Schemas\Schema::class)) {
        class_alias(\Filament\Schemas\Schema::class, \Filament\Infolists\Infolist::class);
    }
}
