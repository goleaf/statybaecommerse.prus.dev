<?php

declare(strict_types=1);

use App\Services\VersionCompatibility\Contracts\TransformationStrategyInterface;
use App\Services\VersionCompatibility\Exceptions\InvalidFileException;
use App\Services\VersionCompatibility\FileProcessor;
use App\Services\VersionCompatibility\TransformationResult;
use App\Services\VersionCompatibilityService;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;

// Prevent Filament from auto-loading during unit tests
if (! defined('LARAVEL_START')) {
    define('LARAVEL_START', microtime(true));
}

beforeEach(function () {
    $this->fileProcessor = Mockery::mock(FileProcessor::class);
    $this->filesystem = Mockery::mock(Filesystem::class);
    $this->cache = Mockery::mock(CacheRepository::class);
    $this->config = Mockery::mock(ConfigRepository::class);

    // Default config values
    $this->config->shouldReceive('get')
        ->with('version-compatibility.cache.prefix', 'filament_transform')
        ->andReturn('filament_transform');

    $this->config->shouldReceive('get')
        ->with('version-compatibility.cache.ttl', 3600)
        ->andReturn(3600);

    $this->config->shouldReceive('get')
        ->with('version-compatibility.logging.slow_threshold_ms', 100)
        ->andReturn(100);

    $this->config->shouldReceive('get')
        ->with('version-compatibility.logging.log_all_transformations', false)
        ->andReturn(false);

    $this->config->shouldReceive('get')
        ->with('version-compatibility.security', [])
        ->andReturn([
            'allowed_extensions'           => ['php'],
            'max_file_size'                => 1024 * 1024,
            'disable_path_traversal_check' => false,
        ]);

    $this->service = new VersionCompatibilityService(
        $this->fileProcessor,
        $this->filesystem,
        $this->cache,
        $this->config
    );
});

describe('VersionCompatibilityService', function () {
    describe('transformContent', function () {
        it('transforms content successfully with caching', function () {
            $content = '<?php class Test {}';
            $expectedResult = new TransformationResult($content, false, []);

            $this->cache->shouldReceive('remember')
                ->once()
                ->with(Mockery::type('string'), 3600, Mockery::type('Closure'))
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            $result = $this->service->transformContent($content);

            expect($result)->toBeInstanceOf(TransformationResult::class);
            expect($result->getContent())->toBe($content);
        });

        it('throws exception for empty content', function () {
            expect(fn () => $this->service->transformContent(''))
                ->toThrow(InvalidArgumentException::class, 'Content cannot be empty');
        });

        it('throws exception for content that is too large', function () {
            $this->config->shouldReceive('get')
                ->with('version-compatibility.security', [])
                ->andReturn(['max_file_size' => 10]);

            $largeContent = str_repeat('a', 20);

            expect(fn () => $this->service->transformContent($largeContent))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('fixResourceFile', function () {
        it('fixes a resource file successfully', function () {
            $filePath = 'app/Filament/Resources/TestResource.php';
            $expectedResult = new TransformationResult('transformed content', true, ['test']);

            $this->filesystem->shouldReceive('exists')
                ->with($filePath)
                ->andReturn(true);

            $this->filesystem->shouldReceive('size')
                ->with($filePath)
                ->andReturn(1000);

            $this->fileProcessor->shouldReceive('processFile')
                ->with($filePath, Mockery::type(Collection::class))
                ->andReturn($expectedResult);

            $result = $this->service->fixResourceFile($filePath);

            expect($result)->toBe($expectedResult);
        });

        it('throws exception for non-existent file', function () {
            $filePath = 'non-existent.php';

            $this->filesystem->shouldReceive('exists')
                ->with($filePath)
                ->andReturn(false);

            expect(fn () => $this->service->fixResourceFile($filePath))
                ->toThrow(InvalidFileException::class);
        });

        it('throws exception for path traversal attempt', function () {
            $filePath = '../../../etc/passwd.php';

            expect(fn () => $this->service->fixResourceFile($filePath))
                ->toThrow(InvalidFileException::class, 'path traversal detected');
        });

        it('throws exception for non-PHP file', function () {
            $filePath = 'test.txt';

            expect(fn () => $this->service->fixResourceFile($filePath))
                ->toThrow(InvalidFileException::class);
        });

        it('throws exception for file that is too large', function () {
            $filePath = 'large-file.php';

            $this->filesystem->shouldReceive('exists')
                ->with($filePath)
                ->andReturn(true);

            $this->filesystem->shouldReceive('size')
                ->with($filePath)
                ->andReturn(2 * 1024 * 1024); // 2MB

            expect(fn () => $this->service->fixResourceFile($filePath))
                ->toThrow(InvalidFileException::class);
        });
    });

    describe('fixAllResourcesInDirectory', function () {
        it('processes directory successfully', function () {
            $directory = 'app/Filament/Resources';
            $results = collect([
                ['file' => 'TestResource.php', 'result' => new TransformationResult('content', true, ['test'])],
            ]);

            $this->filesystem->shouldReceive('exists')
                ->with($directory)
                ->andReturn(true);

            $this->fileProcessor->shouldReceive('processDirectory')
                ->with($directory, Mockery::type(Collection::class))
                ->andReturn($results);

            $result = $this->service->fixAllResourcesInDirectory($directory);

            expect($result)->toBe($results);
        });

        it('throws exception for non-existent directory', function () {
            $directory = 'non-existent-directory';

            $this->filesystem->shouldReceive('exists')
                ->with($directory)
                ->andReturn(false);

            expect(fn () => $this->service->fixAllResourcesInDirectory($directory))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('addStrategy', function () {
        it('adds a new strategy successfully', function () {
            $strategy = Mockery::mock(TransformationStrategyInterface::class);
            $strategy->shouldReceive('getName')->andReturn('TestStrategy');

            $result = $this->service->addStrategy($strategy);

            expect($result)->toBe($this->service);
            expect($this->service->getAvailableStrategies())->toHaveCount(6); // 5 default + 1 added
        });

        it('throws exception for duplicate strategy class', function () {
            $strategy1 = Mockery::mock(TransformationStrategyInterface::class);
            $strategy1->shouldReceive('getName')->andReturn('TestStrategy1');

            $strategy2 = Mockery::mock(get_class($strategy1));
            $strategy2->shouldReceive('getName')->andReturn('TestStrategy2');

            $this->service->addStrategy($strategy1);

            expect(fn () => $this->service->addStrategy($strategy2))
                ->toThrow(InvalidArgumentException::class, 'Strategy already registered');
        });

        it('throws exception for duplicate strategy name', function () {
            $strategy1 = Mockery::mock(TransformationStrategyInterface::class);
            $strategy1->shouldReceive('getName')->andReturn('TestStrategy');

            $strategy2 = Mockery::mock(TransformationStrategyInterface::class);
            $strategy2->shouldReceive('getName')->andReturn('TestStrategy');

            $this->service->addStrategy($strategy1);

            expect(fn () => $this->service->addStrategy($strategy2))
                ->toThrow(InvalidArgumentException::class, 'Strategy name already exists');
        });

        it('throws exception for strategy with empty name', function () {
            $strategy = Mockery::mock(TransformationStrategyInterface::class);
            $strategy->shouldReceive('getName')->andReturn('');

            expect(fn () => $this->service->addStrategy($strategy))
                ->toThrow(InvalidArgumentException::class, 'Strategy name cannot be empty');
        });
    });

    describe('getTransformationStats', function () {
        it('returns comprehensive statistics', function () {
            $stats = $this->service->getTransformationStats();

            expect($stats)->toHaveKeys([
                'service_info',
                'strategies',
                'configuration',
                'cache_stats',
            ]);

            expect($stats['service_info'])->toHaveKeys([
                'available_strategies',
                'cache_prefix',
                'cache_ttl_seconds',
                'slow_threshold_ms',
            ]);

            expect($stats['service_info']['available_strategies'])->toBe(5);
            expect($stats['service_info']['cache_prefix'])->toBe('filament_transform');
        });
    });

    describe('clearCache', function () {
        it('clears cache successfully', function () {
            $result = $this->service->clearCache();

            expect($result)->toBeTrue();
        });
    });

    describe('getAvailableStrategies', function () {
        it('returns all available strategies with details', function () {
            $strategies = $this->service->getAvailableStrategies();

            expect($strategies)->toHaveCount(5);
            expect($strategies->first())->toHaveKeys(['class', 'name']);
        });
    });
});

// Property-based tests
describe('VersionCompatibilityService Property Tests', function () {
    it('always returns the same result for the same content', function () {
        $content = '<?php class Test {}';

        $this->cache->shouldReceive('remember')
            ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

        $result1 = $this->service->transformContent($content);
        $result2 = $this->service->transformContent($content);

        expect($result1->getContent())->toBe($result2->getContent());
        expect($result1->wasTransformed())->toBe($result2->wasTransformed());
    });

    it('transformation is idempotent', function () {
        $content = '<?php class Test {}';

        $this->cache->shouldReceive('remember')
            ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

        $result1 = $this->service->transformContent($content);
        $result2 = $this->service->transformContent($result1->getContent());

        expect($result1->getContent())->toBe($result2->getContent());
    });

    it('adding strategies increases available strategy count', function () {
        $initialCount = $this->service->getAvailableStrategies()->count();

        $strategy = Mockery::mock(TransformationStrategyInterface::class);
        $strategy->shouldReceive('getName')->andReturn('TestStrategy');

        $this->service->addStrategy($strategy);

        expect($this->service->getAvailableStrategies())->toHaveCount($initialCount + 1);
    });
});
