<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PruneDebugbarJsonStorage
{
    private const CLEANUP_INTERVAL_SECONDS = 600;

    private const FILE_TTL_SECONDS = 3600;

    private const LAST_RUN_CACHE_KEY = 'debugbar:json-prune:last-run';

    public function __construct(
        private readonly ConfigRepository $config,
        private readonly CacheRepository $cache,
        private readonly Filesystem $files,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->pruneIfDue();

        /** @var Response $response */
        $response = $next($request);

        return $response;
    }

    private function pruneIfDue(): void
    {
        if (! $this->canPrune()) {
            return;
        }

        $now = now()->getTimestamp();
        $lastRun = (int) $this->cache->get(self::LAST_RUN_CACHE_KEY, 0);

        if ($lastRun > 0 && ($now - $lastRun) < self::CLEANUP_INTERVAL_SECONDS) {
            return;
        }

        $this->cache->forever(self::LAST_RUN_CACHE_KEY, $now);

        $debugbarPath = (string) $this->config->get('debugbar.storage.path', storage_path('debugbar'));

        if ($debugbarPath === '' || ! $this->files->isDirectory($debugbarPath)) {
            return;
        }

        $expiresAtTimestamp = $now - self::FILE_TTL_SECONDS;

        foreach ($this->files->files($debugbarPath) as $file) {
            if ($file->getExtension() !== 'json') {
                continue;
            }

            if ($file->getMTime() <= $expiresAtTimestamp) {
                $this->files->delete($file->getPathname());
            }
        }
    }

    private function canPrune(): bool
    {
        if (! (bool) $this->config->get('debugbar.storage.enabled', true)) {
            return false;
        }

        if ($this->config->get('debugbar.storage.driver', 'file') !== 'file') {
            return false;
        }

        $debugbarEnabled = $this->config->get('debugbar.enabled');
        if ($debugbarEnabled === null) {
            $debugbarEnabled = (bool) $this->config->get('app.debug', false);
        }

        return (bool) $debugbarEnabled;
    }
}
