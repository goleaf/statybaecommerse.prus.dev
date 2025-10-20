<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Command\Command;

it('passes the translation audit', function (): void {
    $exitCode = Artisan::call('i18n:audit');
    $output = Artisan::output();

    expect($exitCode)
        ->withFailMessage("Translation audit failed:\n".$output)
        ->toBe(Command::SUCCESS);

    expect($output)->toContain('All locales are consistent');
});
