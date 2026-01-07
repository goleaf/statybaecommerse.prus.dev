<?php

declare(strict_types=1);

use App\Services\VersionCompatibility\Exceptions\InvalidFileException;
use App\Services\VersionCompatibility\Security\FileSecurityValidator;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Filesystem\Filesystem;

beforeEach(function () {
    $this->filesystem = Mockery::mock(Filesystem::class);
    $this->config = Mockery::mock(ConfigRepository::class);

    // Default security config
    $this->config->shouldReceive('get')
        ->with('version-compatibility.security', [])
        ->andReturn([
            'allowed_extensions'           => ['php'],
            'allowed_mime_types'           => ['text/x-php', 'application/x-php', 'text/plain'],
            'max_file_size'                => 1024 * 1024,
            'disable_path_traversal_check' => false,
            'enable_mime_type_check'       => false, // Disable for unit tests
        ]);

    $this->validator = new FileSecurityValidator(
        $this->filesystem,
        $this->config
    );
});

describe('FileSecurityValidator Basic Tests', function () {
    it('validates content size limits', function () {
        $content = str_repeat('a', 2 * 1024 * 1024); // 2MB

        expect(fn () => $this->validator->validateContent($content))
            ->toThrow(InvalidArgumentException::class, 'exceeds maximum');
    });

    it('rejects empty content', function () {
        expect(fn () => $this->validator->validateContent(''))
            ->toThrow(InvalidArgumentException::class, 'cannot be empty');
    });

    it('accepts valid PHP content', function () {
        $content = '<?php class TestClass { public function test() { return true; } }';

        expect(fn () => $this->validator->validateContent($content))
            ->not->toThrow();
    });

    it('rejects suspicious eval patterns', function () {
        $content = '<?php eval($_POST["code"]); ?>';

        expect(fn () => $this->validator->validateContent($content))
            ->toThrow(InvalidArgumentException::class, 'suspicious patterns');
    });

    it('rejects suspicious exec patterns', function () {
        $content = '<?php exec("rm -rf /"); ?>';

        expect(fn () => $this->validator->validateContent($content))
            ->toThrow(InvalidArgumentException::class, 'suspicious patterns');
    });

    it('rejects suspicious system patterns', function () {
        $content = '<?php system($_GET["cmd"]); ?>';

        expect(fn () => $this->validator->validateContent($content))
            ->toThrow(InvalidArgumentException::class, 'suspicious patterns');
    });

    it('rejects path traversal in file paths', function () {
        $filePath = '../../../etc/passwd.php';

        expect(fn () => $this->validator->validateFilePath($filePath))
            ->toThrow(InvalidFileException::class, 'path traversal detected');
    });

    it('rejects invalid file extensions', function () {
        $filePath = 'malicious.exe';

        expect(fn () => $this->validator->validateFilePath($filePath))
            ->toThrow(InvalidFileException::class, 'not allowed');
    });

    it('validates directory paths', function () {
        $directoryPath = '../../../etc';

        expect(fn () => $this->validator->validateDirectoryPath($directoryPath))
            ->toThrow(InvalidFileException::class, 'path traversal detected');
    });
});

// Property-based security tests
describe('Security Property Tests', function () {
    it('always rejects path traversal patterns', function () {
        $patterns = ['../', '../', '..\\', '~/', '%2e%2e/', '%2e%2e%5c'];

        foreach ($patterns as $pattern) {
            $maliciousPath = $pattern . 'etc/passwd.php';

            expect(fn () => $this->validator->validateFilePath($maliciousPath))
                ->toThrow(InvalidFileException::class);
        }
    });

    it('always rejects dangerous code patterns', function () {
        $patterns = [
            'eval($_POST',
            'exec("rm -rf',
            'system($_GET',
            'shell_exec($cmd',
            'passthru($command',
            'file_get_contents("http://',
            'curl_exec($ch',
            'base64_decode($encoded',
        ];

        foreach ($patterns as $pattern) {
            $maliciousContent = "<?php {$pattern}); ?>";

            expect(fn () => $this->validator->validateContent($maliciousContent))
                ->toThrow(InvalidArgumentException::class);
        }
    });

    it('enforces file size limits consistently', function () {
        $maxSize = 1024 * 1024; // 1MB
        $oversizedContent = str_repeat('a', $maxSize + 1);

        expect(fn () => $this->validator->validateContent($oversizedContent))
            ->toThrow(InvalidArgumentException::class);
    });

    it('only allows whitelisted file extensions', function () {
        $disallowedExtensions = ['exe', 'bat', 'sh', 'js', 'html', 'txt'];

        foreach ($disallowedExtensions as $ext) {
            $filePath = "malicious.{$ext}";

            expect(fn () => $this->validator->validateFilePath($filePath))
                ->toThrow(InvalidFileException::class);
        }
    });
});
