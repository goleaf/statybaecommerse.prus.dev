<?php declare(strict_types=1);

namespace Tests\Feature\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class CodeStyleCommandsTest extends TestCase
{
    use RefreshDatabase;

    private string $testFile;
    private string $testDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testFile = storage_path('test-code-style.php');
        $this->testDir = storage_path('test-code-style-dir');

        File::makeDirectory($this->testDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->testFile)) {
            File::delete($this->testFile);
        }

        if (File::exists($this->testDir)) {
            File::deleteDirectory($this->testDir);
        }

        parent::tearDown();
    }

    public function test_fix_code_style_command_fixes_file(): void
    {
        $content = '<?php

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

        File::put($this->testFile, $content);

        $this
            ->artisan('code-style:fix', ['--path' => $this->testFile])
            ->expectsOutput('🔧 Code Style Fixer')
            ->expectsOutput("Processing file: {$this->testFile}")
            ->assertExitCode(0);

        $fixedContent = File::get($this->testFile);
        $this->assertStringContainsString('protected int|string $property;', $fixedContent);
        $this->assertStringContainsString('$value = 100;', $fixedContent);
        $this->assertStringContainsString('fn(string $value)', $fixedContent);
    }

    public function test_fix_code_style_command_with_dry_run(): void
    {
        $content = '<?php

final class TestClass
{
    protected int | string $property;
}';

        File::put($this->testFile, $content);

        $this
            ->artisan('code-style:fix', [
                '--path' => $this->testFile,
                '--dry-run' => true
            ])
            ->expectsOutput('Running in dry-run mode - no files will be modified')
            ->assertExitCode(0);

        $originalContent = File::get($this->testFile);
        $this->assertStringContainsString('protected int | string $property;', $originalContent);
    }

    public function test_fix_code_style_command_fixes_directory(): void
    {
        $file1 = $this->testDir . '/test1.php';
        $file2 = $this->testDir . '/test2.php';

        File::put($file1, '<?php use App\Models\User; use Illuminate\Support\Facades\Hash;');
        File::put($file2, '<?php $value = 100.00;');

        $this
            ->artisan('code-style:fix', ['--path' => $this->testDir])
            ->expectsOutput('🔧 Code Style Fixer')
            ->expectsOutput("Processing directory: {$this->testDir}")
            ->assertExitCode(0);

        $fixedContent1 = File::get($file1);
        $fixedContent2 = File::get($file2);

        $this->assertStringContainsString('use Illuminate\\', $fixedContent1);
        $this->assertStringContainsString('$value = 100;', $fixedContent2);
    }

    public function test_validate_code_style_command_validates_file(): void
    {
        $content = '<?php

final class TestClass
{
    protected int | string $property;
    
    public function test(): void
    {
        $value = 100.00;
    }
}';

        File::put($this->testFile, $content);

        $this
            ->artisan('code-style:validate', ['--path' => $this->testFile])
            ->expectsOutput('🔍 Code Style Validator')
            ->expectsOutput("Validating file: {$this->testFile}")
            ->assertExitCode(0);
    }

    public function test_validate_code_style_command_with_strict_mode(): void
    {
        $content = '<?php

final class TestClass
{
    protected int | string $property;
}';

        File::put($this->testFile, $content);

        $this
            ->artisan('code-style:validate', [
                '--path' => $this->testFile,
                '--strict' => true
            ])
            ->expectsOutput('🔍 Code Style Validator')
            ->expectsOutput('Code style validation failed!')
            ->assertExitCode(1);
    }

    public function test_validate_code_style_command_passes_with_no_violations(): void
    {
        $content = '<?php

use Illuminate\Support\Facades\Hash;
use App\Models\User;

final class TestClass
{
    protected int|string $property;

    public function test(): void
    {
        $value = 100;
    }
}
';

        File::put($this->testFile, $content);

        $this
            ->artisan('code-style:validate', [
                '--path' => $this->testFile,
                '--strict' => true
            ])
            ->expectsOutput('🔍 Code Style Validator')
            ->expectsOutput('✅ Code style validation passed!')
            ->assertExitCode(0);
    }

    public function test_validate_code_style_command_with_report(): void
    {
        $content = '<?php

final class TestClass
{
    protected int | string $property;
}';

        File::put($this->testFile, $content);

        $this
            ->artisan('code-style:validate', [
                '--path' => $this->testFile,
                '--report' => true
            ])
            ->expectsOutput('🔍 Code Style Validator')
            ->expectsOutput('📄 Detailed report saved to:')
            ->assertExitCode(0);

        $reportPath = storage_path('logs/code-style-validation-report.json');
        $this->assertTrue(File::exists($reportPath));

        $report = json_decode(File::get($reportPath), true);
        $this->assertArrayHasKey('total_violations', $report);
        $this->assertArrayHasKey('violations_by_type', $report);
    }

    public function test_fix_code_style_command_with_report(): void
    {
        $content = '<?php

final class TestClass
{
    protected int | string $property;
}';

        File::put($this->testFile, $content);

        $this
            ->artisan('code-style:fix', [
                '--path' => $this->testFile,
                '--report' => true
            ])
            ->expectsOutput('🔧 Code Style Fixer')
            ->expectsOutput('📄 Detailed report saved to:')
            ->assertExitCode(0);

        $reportPath = storage_path('logs/code-style-report.json');
        $this->assertTrue(File::exists($reportPath));

        $report = json_decode(File::get($reportPath), true);
        $this->assertArrayHasKey('total_issues', $report);
        $this->assertArrayHasKey('issues_by_type', $report);
    }

    public function test_commands_handle_non_existent_path(): void
    {
        $nonExistentPath = storage_path('non-existent-path');

        $this
            ->artisan('code-style:fix', ['--path' => $nonExistentPath])
            ->expectsOutput('Path does not exist: ' . $nonExistentPath)
            ->assertExitCode(1);

        $this
            ->artisan('code-style:validate', ['--path' => $nonExistentPath])
            ->expectsOutput('Path does not exist: ' . $nonExistentPath)
            ->assertExitCode(1);
    }

    public function test_commands_handle_custom_extensions(): void
    {
        $phpFile = $this->testDir . '/test.php';
        $txtFile = $this->testDir . '/test.txt';

        File::put($phpFile, '<?php $value = 100.00;');
        File::put($txtFile, 'This is a text file');

        $this
            ->artisan('code-style:fix', [
                '--path' => $this->testDir,
                '--extensions' => 'php,txt'
            ])
            ->expectsOutput('🔧 Code Style Fixer')
            ->assertExitCode(0);

        // Only PHP file should be processed for code style fixes
        $phpContent = File::get($phpFile);
        $this->assertStringContainsString('$value = 100;', $phpContent);
    }
}
