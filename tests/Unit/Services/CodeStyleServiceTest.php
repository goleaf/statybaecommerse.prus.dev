<?php

declare(strict_types=1);

use App\Services\CodeStyleService;

it('validates and fixes file rules', function (): void {
    $tmp = sys_get_temp_dir() . '/codestyle_tmp_' . bin2hex(random_bytes(5)) . '.php';
    $content = <<<'PHP'
<?php

use App\Services\CodeStyleService;
use Illuminate\Support\Facades\File;

final class Sample
{
    public function example(): int | string
    {
        $fn = fn ( $x ) => $x;  
        $n = 10.00;
        return $n > 0 ? 1 : '0';
    }
}
PHP;

    file_put_contents($tmp, $content);
    expect(file_exists($tmp))->toBeTrue();

    $service = new CodeStyleService;
    $violations = $service->validateFile($tmp);
    expect($violations)->not->toBeEmpty();

    $fixes = $service->fixFile($tmp);
    expect($fixes)->not->toBeEmpty();

    $after = $service->validateFile($tmp);
    expect($after)->toBeArray()->toBeEmpty();

    @unlink($tmp);
});
