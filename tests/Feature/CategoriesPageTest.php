<?php

use function Pest\Laravel\get;

it('loads categories index page', function () {
    $response = get(route('categories.index'));
    $response->assertOk();
});
