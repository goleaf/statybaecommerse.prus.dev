<?php

declare(strict_types=1);

// This bootstrap-level shim restores compatibility for Filament plugins that
// still reference pre-v4 class names during Composer package discovery.
// Keeping the aliases outside of PSR-4 autoloaded directories prevents
// Composer from flagging the synthetic classes while ensuring they exist
// whenever the application starts.

namespace {
    if (! class_exists(\Filament\Forms\Form::class) && class_exists(\Filament\Schemas\Schema::class)) {
        class_alias(\Filament\Schemas\Schema::class, \Filament\Forms\Form::class);
    }

    if (! class_exists(\Filament\Infolists\Infolist::class) && class_exists(\Filament\Schemas\Schema::class)) {
        class_alias(\Filament\Schemas\Schema::class, \Filament\Infolists\Infolist::class);
    }

    if (! class_exists(\Filament\Tables\Table::class) && class_exists(\Filament\Resources\Table::class)) {
        class_alias(\Filament\Resources\Table::class, \Filament\Tables\Table::class);
    }

    if (! class_exists(\Filament\Forms\Components\Flatpickr::class) && class_exists(\Coolsam\Flatpickr\Forms\Components\Flatpickr::class)) {
        // Preserve the legacy component namespace so third-party discovery hooks can resolve Flatpickr during upgrades.
        class_alias(\Coolsam\Flatpickr\Forms\Components\Flatpickr::class, \Filament\Forms\Components\Flatpickr::class);
    }

    $legacyTableActions = [
        \Filament\Tables\Actions\Action::class          => \Filament\Actions\Action::class,
        \Filament\Tables\Actions\ActionGroup::class     => \Filament\Actions\ActionGroup::class,
        \Filament\Tables\Actions\AttachAction::class    => \Filament\Actions\AttachAction::class,
        \Filament\Tables\Actions\BulkAction::class      => \Filament\Actions\BulkAction::class,
        \Filament\Tables\Actions\BulkActionGroup::class => \Filament\Actions\BulkActionGroup::class,
        \Filament\Tables\Actions\CreateAction::class    => \Filament\Actions\CreateAction::class,
        \Filament\Tables\Actions\DeleteAction::class    => \Filament\Actions\DeleteAction::class,
        \Filament\Tables\Actions\DeleteBulkAction::class => \Filament\Actions\DeleteBulkAction::class,
        \Filament\Tables\Actions\DetachAction::class    => \Filament\Actions\DetachAction::class,
        \Filament\Tables\Actions\EditAction::class      => \Filament\Actions\EditAction::class,
        \Filament\Tables\Actions\ForceDeleteBulkAction::class => \Filament\Actions\ForceDeleteBulkAction::class,
        \Filament\Tables\Actions\RestoreBulkAction::class     => \Filament\Actions\RestoreBulkAction::class,
        \Filament\Tables\Actions\ViewAction::class      => \Filament\Actions\ViewAction::class,
    ];

    foreach ($legacyTableActions as $legacyClass => $modernClass) {
        if (! class_exists($legacyClass) && class_exists($modernClass)) {
            class_alias($modernClass, $legacyClass);
        }
    }

    if (class_exists(\Livewire\Features\SupportTesting\Testable::class)) {
        \Livewire\Features\SupportTesting\Testable::macro('assertSchemaExists', function (?string $name = null): static {
            $name ??= method_exists($this->instance(), 'getDefaultTestingSchemaName')
                ? $this->instance()->getDefaultTestingSchemaName()
                : null;

            $name ??= 'form';

            /** @var \Filament\Schemas\Schema|null $schema */
            $schema = $this->instance()->{$name} ?? null;

            \Illuminate\Testing\Assert::assertInstanceOf(
                \Filament\Schemas\Schema::class,
                $schema,
                sprintf(
                    'Failed asserting that a schema with the name [%s] exists on the [%s] component.',
                    (string) $name,
                    $this->instance()::class
                )
            );

            return $this;
        });
    }
}

namespace Filament\Forms\Components {
    if (! class_exists(Section::class) && class_exists(\Filament\Schemas\Components\Section::class)) {
        class_alias(\Filament\Schemas\Components\Section::class, Section::class);
    }

    if (! class_exists(Grid::class) && class_exists(\Filament\Schemas\Components\Grid::class)) {
        class_alias(\Filament\Schemas\Components\Grid::class, Grid::class);
    }

    if (! class_exists(SchemaSection::class) && class_exists(Section::class)) {
        class_alias(Section::class, SchemaSection::class);
    }

    if (! class_exists(SchemaGrid::class) && class_exists(Grid::class)) {
        class_alias(Grid::class, SchemaGrid::class);
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
