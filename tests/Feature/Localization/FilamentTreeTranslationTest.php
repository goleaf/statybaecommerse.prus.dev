<?php

declare(strict_types=1);

describe('Filament Tree Translation', function () {
    it('has Lithuanian translations for filament tree actions and buttons', function () {
        $keys = [
            'filament-tree::filament-tree.button.expand_all',
            'filament-tree::filament-tree.button.collapse_all',
            'filament-tree::filament-tree.button.save',
            'filament-tree::filament-tree.components.tree.buttons.select_all.label',
            'filament-tree::filament-tree.components.tree.buttons.deselect_all.label',
            'filament-tree::filament-tree.components.tree.buttons.expand_all.label',
            'filament-tree::filament-tree.components.tree.buttons.collapse_all.label',
            'filament-tree::filament-tree.actions.delete.confirmation.with_children',
        ];

        foreach ($keys as $key) {
            $translation = __($key, [], 'lt');

            expect($translation)
                ->toBeString()
                ->not->toBe($key)
                ->not->toBe('');
        }
    });
});

