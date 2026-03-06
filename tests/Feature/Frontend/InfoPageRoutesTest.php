<?php

declare(strict_types=1);

it('renders the new informational footer pages', function (string $routeName, string $expectedTitle): void {
    $this
        ->get(route($routeName))
        ->assertOk()
        ->assertSeeText($expectedTitle)
        ->assertSeeText('Reikia pagalbos?');
})->with([
    ['frontend.info.faq', 'DUK'],
    ['frontend.info.payment-methods', 'Apmokėjimo būdai'],
    ['frontend.info.popular-products', 'Populiariausios prekės'],
    ['frontend.info.building-materials', 'Statybinės medžiagos'],
    ['frontend.info.tools-equipment', 'Įrankiai ir įranga'],
    ['frontend.info.special-offers', 'Specialūs pasiūlymai ir akcijos'],
    ['frontend.info.services-for-craftsmen', 'Paslaugos meistrams'],
]);
