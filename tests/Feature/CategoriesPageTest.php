<?php

use function Pest\Laravel\get;

it('redirects categories index to localized version', function () {
    $response = get(route('categories.index'));
    $response->assertRedirect('/' . app()->getLocale() . '/categories');
});

it('loads localized categories index page', function () {
    $response = get(route('localized.categories.index', ['locale' => app()->getLocale()]));
    $response->assertOk();
});
