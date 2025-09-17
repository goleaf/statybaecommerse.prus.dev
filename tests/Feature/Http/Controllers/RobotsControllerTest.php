<?php

declare(strict_types=1);

it('serves robots.txt', function (): void {
    $response = $this->get('/robots.txt');

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/plain');
});
