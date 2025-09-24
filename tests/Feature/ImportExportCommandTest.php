<?php

declare(strict_types=1);

it('runs catalog:xml export and import', function (): void {
    $tmp = base_path('storage/cmd-catalog.xml');
    @unlink($tmp);
    $this->artisan('catalog:xml export '.$tmp.' --only=all')
        ->assertSuccessful();
    expect(file_exists($tmp))->toBeTrue();

    $this->artisan('catalog:xml import '.$tmp.' --only=all')
        ->assertSuccessful();
});
