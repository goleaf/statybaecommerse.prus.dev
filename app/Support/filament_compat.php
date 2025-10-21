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

namespace Hydrat\TableLayoutToggle\Concerns {
    if (! trait_exists(HasToggleableTable::class)) {
        trait HasToggleableTable
        {
            public ?string $layoutView = null;

            public function initializeHasToggleableTable(): void
            {
                // No-op fallback.
            }

            public function updatedLayoutView(mixed $value): void
            {
                // No-op fallback.
            }

            public function bootHasToggleableTable(): void
            {
                $this->layoutView ??= $this->getDefaultLayoutView();
            }

            public function configurePersister(): void
            {
                // No-op fallback.
            }

            public function bootedHasToggleableTable(): void
            {
                // No-op fallback.
            }

            public function getDefaultLayoutView(): string
            {
                return 'list';
            }

            public function isGridLayout(): bool
            {
                return $this->getLayoutView() === 'grid';
            }

            public function isListLayout(): bool
            {
                return $this->getLayoutView() === 'list';
            }

            public function getLayoutView(): string
            {
                return $this->layoutView ?? $this->getDefaultLayoutView();
            }

            protected function registerLayoutViewToogleActionHook(string $filamentHook): void
            {
                // No-op fallback.
            }

            public function changeLayoutView(): void
            {
                $this->layoutView = $this->isListLayout() ? 'grid' : 'list';
            }
        }
    }
}

namespace {
    if (! class_exists(\Filament\Infolists\Infolist::class) && class_exists(\Filament\Schemas\Schema::class)) {
        class_alias(\Filament\Schemas\Schema::class, \Filament\Infolists\Infolist::class);
    }
}
