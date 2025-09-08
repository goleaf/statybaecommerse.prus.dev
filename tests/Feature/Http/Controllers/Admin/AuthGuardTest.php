<?php declare(strict_types=1);

it('guards admin translation update routes for unauthenticated users', function (): void {
    foreach ([
        ['admin.legal.translations.save', ['id' => 1, 'lang' => 'en']],
        ['admin.brands.translations.save', ['id' => 1, 'lang' => 'en']],
        ['admin.categories.translations.save', ['id' => 1, 'lang' => 'en']],
        ['admin.collections.translations.save', ['id' => 1, 'lang' => 'en']],
        ['admin.products.translations.save', ['id' => 1, 'lang' => 'en']],
        ['admin.attributes.translations.save', ['id' => 1, 'lang' => 'en']],
        ['admin.attribute-values.translations.save', ['id' => 1, 'lang' => 'en']],
    ] as [$name, $params]) {
        $response = $this->put(route($name, array_merge(['locale' => 'en'], $params)));
        $response->assertRedirect();
    }
});
