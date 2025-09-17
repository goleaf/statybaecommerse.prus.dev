<?php

declare(strict_types=1);

it('guards exports routes for unauthenticated users', function (): void {
    $response = $this->get(route('exports.index'));
    $response->assertRedirect();
});
