<?php

declare(strict_types=1);

namespace App\Filament\Components;

use Novadaemon\FilamentCombobox\Combobox as BaseCombobox;

final class Combobox extends BaseCombobox
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->boxSearchs();
        $this->showLabels(true);
    }
}
