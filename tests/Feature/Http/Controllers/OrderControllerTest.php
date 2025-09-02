<?php declare(strict_types=1);

it('guards order confirmation route for unauthenticated users', function (): void {
    $response = $this->get(route('checkout.confirmation', ['locale' => 'en', 'number' => 'TEST123']));
    $response->assertRedirect();
});
