<?php

declare(strict_types=1);

if (! class_exists(\Filament\Forms\Form::class) && class_exists(\Filament\Schemas\Schema::class)) {
    class_alias(\Filament\Schemas\Schema::class, \Filament\Forms\Form::class);
}
