<?php declare(strict_types=1);

it('redirects root to locale home', function (): void {
    $this->get('/')->assertRedirect('/en');
});

it('redirects non-localized resource indexes to localized versions', function (): void {
    $this->get('/brands')->assertRedirect('/en/brands');
    $this->get('/locations')->assertRedirect('/en/locations');
    $this->get('/categories')->assertRedirect('/en/categories');
    $this->get('/collections')->assertRedirect('/en/collections');
    $this->get('/search')->assertRedirect('/en/search');
});

it('redirects non-localized legal to localized version', function (): void {
    $this->get('/legal/privacy')->assertRedirect('/en/legal/privacy');
});

it('redirects localized cpanel paths to non-localized cpanel', function (): void {
    $this->get('/en/cpanel')->assertRedirect('/cpanel/login');
    $this->get('/en/cpanel/anything')->assertRedirect('/cpanel/anything');
});
