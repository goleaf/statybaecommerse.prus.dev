<?php

declare(strict_types=1);

use App\Models\DocumentTemplate;
use App\Services\DocumentService;
use Tests\TestCase;

uses(TestCase::class);

it('renders template with variables', function () {
    $svc = app(DocumentService::class);
    $tpl = new DocumentTemplate;
    $tpl->content = 'Hello $NAME';

    $html = $svc->renderTemplate($tpl, ['$NAME' => 'World']);
    expect($html)->toBe('Hello World');
});
