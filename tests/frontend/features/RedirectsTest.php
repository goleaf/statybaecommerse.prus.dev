<?php

declare(strict_types=1);

it('redirects root to locale home', function (): void {
    $this->get('/')->assertRedirect('/lt');
});

it('redirects non-localized resource indexes to localized versions', function (): void {
    $this->get('/brands')->assertRedirect('/lt/brands');
    $this->get('/locations')->assertRedirect('/lt/locations');
    $this->get('/categories')->assertRedirect('/lt/categories');
    $this->get('/collections')->assertRedirect('/lt/collections');
    $this->get('/search')->assertRedirect('/lt/search');
});

it('redirects non-localized legal to localized version', function (): void {
    $this->get('/legal/privacy')->assertRedirect('/lt/legal/privacy');
});

it('redirects localized cpanel paths to non-localized cpanel', function (): void {
    $this->get('/en/cpanel')->assertRedirect('/cpanel/login');
    $this->get('/en/cpanel/anything')->assertRedirect('/cpanel/anything');
});
