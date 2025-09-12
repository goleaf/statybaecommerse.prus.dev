#!/usr/bin/env php
<?php

/**
 * Code Style Fixer Script
 *
 * This script demonstrates how to use the CodeStyleService
 * to fix code style issues in your project.
 */
require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\CodeStyleService;
use Illuminate\Foundation\Application;

// Bootstrap Laravel
$app = new Application(realpath(__DIR__ . '/..'));
$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get the code style service
$codeStyleService = $app->make(CodeStyleService::class);

// Example usage
echo "🔧 Code Style Fixer Script\n";
echo "==========================\n\n";

// Fix a specific file
$testFile = storage_path('test-file.php');
$testContent = '<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class TestClass
{
    protected int | string $property;
    
    public function test(): void
    {
        $value = 100.00;
        $callback = fn (string $value) => $value;
    }
}';

file_put_contents($testFile, $testContent);

echo "📄 Original file content:\n";
echo $testContent . "\n\n";

echo "🔍 Validating file...\n";
$violations = $codeStyleService->validateFile($testFile);

if (!empty($violations)) {
    echo '⚠️  Found ' . count($violations) . " violations:\n";
    foreach ($violations as $violation) {
        echo "   Line {$violation['line']}: {$violation['message']}\n";
    }
    echo "\n";
} else {
    echo "✅ No violations found!\n\n";
}

echo "🔧 Fixing file...\n";
$fixes = $codeStyleService->fixFile($testFile);

if (!empty($fixes)) {
    echo '✅ Applied ' . count($fixes) . " fixes:\n";
    foreach ($fixes as $fix) {
        echo "   - {$fix['message']}\n";
    }
    echo "\n";
} else {
    echo "✅ No fixes needed!\n\n";
}

echo "📄 Fixed file content:\n";
echo file_get_contents($testFile) . "\n";

// Clean up
unlink($testFile);

echo "🎉 Code style fixing completed!\n";
