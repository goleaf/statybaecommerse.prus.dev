<?php

declare(strict_types=1);

use App\Services\VersionCompatibility\Exceptions\InvalidFileException;
use App\Services\VersionCompatibility\Security\FileSecurityValidator;
use App\Services\VersionCompatibility\Security\RateLimiter;
use Illuminate\Cache\RateLimiter as LaravelRateLimiter;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->filesystem = Mockery::mock(Filesystem::class);
    $this->config = Mockery::mock(ConfigRepository::class);
    $this->laravelRateLimiter = Mockery::mock(LaravelRateLimiter::class);

    // Default security config
    $this->config->shouldReceive('get')
        ->with('version-compatibility.security', [])
        ->andReturn([
            'allowed_extensions'           => ['php'],
            'allowed_mime_types'           => ['text/x-php', 'application/x-php', 'text/plain'],
            'max_file_size'                => 1024 * 1024,
            'disable_path_traversal_check' => false,
            'enable_mime_type_check'       => true,
        ]);

    $this->securityValidator = new FileSecurityValidator(
        $this->filesystem,
        $this->config
    );

    $this->rateLimiter = new RateLimiter(
        $this->laravelRateLimiter,
        [
            'max_attempts'         => 10,
            'decay_minutes'        => 60,
            'enable_ip_limiting'   => true,
            'enable_user_limiting' => true,
        ]
    );
});

describe('FileSecurityValidator', function () {
    describe('validateFilePath', function () {
        it('validates a legitimate PHP file successfully', function () {
            $filePath = 'app/Services/TestService.php';

            $this->filesystem->shouldReceive('exists')
                ->with($filePath)
                ->andReturn(true);

            $this->filesystem->shouldReceive('size')
                ->with($filePath)
                ->andReturn(1000);

            // Mock finfo functions for MIME type check
            if (! function_exists('finfo_open')) {
                function finfo_open($options)
                {
                    return true;
                }
                function finfo_file($finfo, $filename)
                {
                    return 'text/x-php';
                }
                function finfo_close($finfo)
                {
                    return true;
                }
            }

            expect(fn () => $this->securityValidator->validateFilePath($filePath))
                ->not->toThrow();
        });

        it('throws exception for path traversal attempt', function () {
            $filePath = '../../../etc/passwd.php';

            expect(fn () => $this->securityValidator->validateFilePath($filePath))
                ->toThrow(InvalidFileException::class, 'path traversal detected');
        });

        it('throws exception for invalid file extension', function () {
            $filePath = 'malicious.exe';

            expect(fn () => $this->securityValidator->validateFilePath($filePath))
                ->toThrow(InvalidFileException::class, 'not allowed');
        });

        it('throws exception for non-existent file', function () {
            $filePath = 'non-existent.php';

            $this->filesystem->shouldReceive('exists')
                ->with($filePath)
                ->andReturn(false);

            expect(fn () => $this->securityValidator->validateFilePath($filePath))
                ->toThrow(InvalidFileException::class, 'does not exist');
        });

        it('throws exception for file that is too large', function () {
            $filePath = 'large-file.php';

            $this->filesystem->shouldReceive('exists')
                ->with($filePath)
                ->andReturn(true);

            $this->filesystem->shouldReceive('size')
                ->with($filePath)
                ->andReturn(2 * 1024 * 1024); // 2MB

            expect(fn () => $this->securityValidator->validateFilePath($filePath))
                ->toThrow(InvalidFileException::class, 'exceeds maximum');
        });
    });

    describe('validateContent', function () {
        it('validates legitimate PHP content', function () {
            $content = '<?php class TestClass {}';

            expect(fn () => $this->securityValidator->validateContent($content))
                ->not->toThrow();
        });

        it('throws exception for empty content', function () {
            expect(fn () => $this->securityValidator->validateContent(''))
                ->toThrow(InvalidArgumentException::class, 'cannot be empty');
        });

        it('throws exception for content that is too large', function () {
            $largeContent = str_repeat('a', 2 * 1024 * 1024); // 2MB

            expect(fn () => $this->securityValidator->validateContent($largeContent))
                ->toThrow(InvalidArgumentException::class, 'exceeds maximum');
        });

        it('throws exception for suspicious content patterns', function () {
            $suspiciousContent = '<?php eval($_POST["code"]); ?>';

            expect(fn () => $this->securityValidator->validateContent($suspiciousContent))
                ->toThrow(InvalidArgumentException::class, 'suspicious patterns');
        });
    });

    describe('validateDirectoryPath', function () {
        it('validates legitimate directory path', function () {
            $directoryPath = 'app/Services';

            $this->filesystem->shouldReceive('exists')
                ->with($directoryPath)
                ->andReturn(true);

            // Mock is_dir function
            if (! function_exists('is_dir')) {
                function is_dir($path)
                {
                    return true;
                }
            }

            expect(fn () => $this->securityValidator->validateDirectoryPath($directoryPath))
                ->not->toThrow();
        });

        it('throws exception for path traversal in directory', function () {
            $directoryPath = '../../../etc';

            expect(fn () => $this->securityValidator->validateDirectoryPath($directoryPath))
                ->toThrow(InvalidFileException::class, 'path traversal detected');
        });

        it('throws exception for non-existent directory', function () {
            $directoryPath = 'non-existent-directory';

            $this->filesystem->shouldReceive('exists')
                ->with($directoryPath)
                ->andReturn(false);

            expect(fn () => $this->securityValidator->validateDirectoryPath($directoryPath))
                ->toThrow(InvalidArgumentException::class, 'does not exist');
        });
    });
});

describe('RateLimiter', function () {
    beforeEach(function () {
        $this->request = Mockery::mock(Request::class);
        $this->request->shouldReceive('ip')->andReturn('127.0.0.1');
        $this->request->shouldReceive('user')->andReturn(null);
        $this->request->shouldReceive('userAgent')->andReturn('Test Agent');
    });

    describe('checkRateLimit', function () {
        it('allows requests within rate limit', function () {
            $this->laravelRateLimiter->shouldReceive('tooManyAttempts')
                ->with('version_compat_ip:127.0.0.1', 10)
                ->andReturn(false);

            expect(fn () => $this->rateLimiter->checkRateLimit($this->request))
                ->not->toThrow();
        });

        it('throws exception when rate limit exceeded', function () {
            $this->laravelRateLimiter->shouldReceive('tooManyAttempts')
                ->with('version_compat_ip:127.0.0.1', 10)
                ->andReturn(true);

            $this->laravelRateLimiter->shouldReceive('availableIn')
                ->with('version_compat_ip:127.0.0.1')
                ->andReturn(300);

            expect(fn () => $this->rateLimiter->checkRateLimit($this->request))
                ->toThrow(RuntimeException::class, 'Too many transformation attempts');
        });
    });

    describe('recordAttempt', function () {
        it('records successful attempt', function () {
            $this->laravelRateLimiter->shouldReceive('hit')
                ->with('version_compat_ip:127.0.0.1', 3600)
                ->once();

            $this->rateLimiter->recordAttempt($this->request);
        });
    });

    describe('getRemainingAttempts', function () {
        it('returns remaining attempts', function () {
            $this->laravelRateLimiter->shouldReceive('remaining')
                ->with('version_compat_ip:127.0.0.1', 10)
                ->andReturn(5);

            $remaining = $this->rateLimiter->getRemainingAttempts($this->request);

            expect($remaining)->toBe(5);
        });
    });

    describe('clearRateLimit', function () {
        it('clears rate limit successfully', function () {
            $this->laravelRateLimiter->shouldReceive('clear')
                ->with('version_compat_ip:127.0.0.1')
                ->once();

            $this->rateLimiter->clearRateLimit($this->request);
        });
    });
});

// Property-based security tests
describe('Security Property Tests', function () {
    it('path traversal patterns are always rejected', function () {
        $dangerousPatterns = ['../', '../', '..\\', '~/', '%2e%2e/', '%2e%2e%5c'];

        foreach ($dangerousPatterns as $pattern) {
            $maliciousPath = $pattern . 'etc/passwd.php';

            expect(fn () => $this->securityValidator->validateFilePath($maliciousPath))
                ->toThrow(InvalidFileException::class);
        }
    });

    it('suspicious code patterns are always rejected', function () {
        $suspiciousPatterns = [
            'eval($_POST',
            'exec("rm -rf',
            'system($_GET',
            'shell_exec($cmd',
            'passthru($command',
            'file_get_contents("http://',
            'curl_exec($ch',
            'base64_decode($encoded',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            $maliciousContent = "<?php {$pattern}); ?>";

            expect(fn () => $this->securityValidator->validateContent($maliciousContent))
                ->toThrow(InvalidArgumentException::class);
        }
    });

    it('file size limits are always enforced', function () {
        $maxSize = 1024 * 1024; // 1MB
        $oversizedContent = str_repeat('a', $maxSize + 1);

        expect(fn () => $this->securityValidator->validateContent($oversizedContent))
            ->toThrow(InvalidArgumentException::class);
    });

    it('only allowed file extensions are accepted', function () {
        $allowedExtensions = ['php'];
        $disallowedExtensions = ['exe', 'bat', 'sh', 'js', 'html', 'txt'];

        foreach ($disallowedExtensions as $ext) {
            $filePath = "malicious.{$ext}";

            expect(fn () => $this->securityValidator->validateFilePath($filePath))
                ->toThrow(InvalidFileException::class);
        }
    });
});
