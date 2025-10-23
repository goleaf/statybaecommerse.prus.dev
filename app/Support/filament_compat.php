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

namespace Icetalker\FilamentTableRepeatableEntry\Infolists\Components {

    use Filament\Infolists\Components\RepeatableEntry;

    if (! class_exists(TableRepeatableEntry::class) && class_exists(RepeatableEntry::class)) {
        class TableRepeatableEntry extends RepeatableEntry
        {
            public static function make(?string $name = null): static
            {
                return parent::make($name);
            }
        }
    }
}

namespace SolutionForest\TabLayoutPlugin\Components {

    if (! class_exists(Tabs::class) && class_exists(\Filament\Forms\Components\Tabs::class)) {
        class Tabs extends \Filament\Forms\Components\Tabs {}
    }
}

namespace SolutionForest\TabLayoutPlugin\Components\Tabs {

    if (! class_exists(Tab::class) && class_exists(\Filament\Forms\Components\Tabs\Tab::class)) {
        class Tab extends \Filament\Forms\Components\Tabs\Tab {}
    }
}

namespace SolutionForest\TabLayoutPlugin\Widgets {

    if (! class_exists(TabsWidget::class)) {
        if (class_exists(\Filament\Widgets\Widget::class)) {
            abstract class TabsWidget extends \Filament\Widgets\Widget {}
        } else {
            abstract class TabsWidget {}
        }
    }
}

namespace SolutionForest\TabLayoutPlugin\Schemas {

    if (! class_exists(SimpleTabSchema::class)) {
        class SimpleTabSchema
        {
            public static function make(?string $label = null, ?string $id = null): self
            {
                return new self;
            }

            public function livewireComponent(string $component, array $data = []): self
            {
                return $this;
            }

            public function icon(?string $icon): self
            {
                return $this;
            }

            public function badge(?string $badge): self
            {
                return $this;
            }
        }
    }
}

namespace Asmit\ResizedColumn {
    if (! trait_exists(HasResizableColumn::class) && trait_exists(\App\Support\FilamentCompat\HasResizableColumn::class)) {
        class_alias(\App\Support\FilamentCompat\HasResizableColumn::class, HasResizableColumn::class);
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

namespace Hydrat\TableLayoutToggle\Concerns {
    if (! trait_exists(HasToggleableTable::class)) {
        trait HasToggleableTable
        {
            public ?string $layoutView = null;

            public function initializeHasToggleableTable(): void
            {
                // No-op fallback when the Table Layout Toggle plugin is unavailable.
            }

            public function updatedLayoutView(mixed $value): void
            {
                // No-op fallback when the Table Layout Toggle plugin is unavailable.
            }

            public function bootHasToggleableTable(): void
            {
                // No-op fallback when the Table Layout Toggle plugin is unavailable.
            }

            public function configurePersister(): void
            {
                // No-op fallback when the Table Layout Toggle plugin is unavailable.
            }

            public function bootedHasToggleableTable(): void
            {
                // No-op fallback when the Table Layout Toggle plugin is unavailable.
            }

            public function getDefaultLayoutView(): string
            {
                return 'list';
            }

            public function isGridLayout(): bool
            {
                return false;
            }

            public function isListLayout(): bool
            {
                return true;
            }

            public function getLayoutView(): string
            {
                return $this->layoutView ?? $this->getDefaultLayoutView();
            }

            protected function registerLayoutViewToogleActionHook(string $filamentHook): void
            {
                // No-op fallback when the Table Layout Toggle plugin is unavailable.
            }

            public function changeLayoutView(): void
            {
                $this->layoutView = $this->isListLayout() ? 'grid' : 'list';
            }
        }
    }
}
