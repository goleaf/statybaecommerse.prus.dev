<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class CheckRefreshDatabaseCommand extends Command
{
    /**
     * @var array<string, string>
     */
    private const MUTATION_PATTERNS = [
        '::factory('      => '/::factory\s*\(/',
        '->factory('      => '/->factory\s*\(/',
        '->seed('         => '/->seed\s*\(/',
        'HTTP verb'       => '/->\s*(?:post|put|patch|delete)(?:Json)?\s*\(/i',
        'HTTP call verb'  => "/->\s*call\s*\(\s*['\"]\s*(?:post|put|patch|delete)/i",
        'artisan db:seed' => "/->\s*artisan\s*\(\s*['\"]db:seed/i",
    ];

    /**
     * @var list<string>
     */
    private const DATABASE_REFRESH_TRAITS = [
        'RefreshDatabase',
        'DatabaseMigrations',
        'DatabaseTransactions',
        'DatabaseTruncation',
    ];

    /**
     * @var string
     */
    protected $signature = 'tests:check-refresh-database
        {path=tests/Feature : The directory to scan for feature tests}
        {--fail-on-find : Exit with a non-zero status code when files are flagged}
        {--include-all : List every file missing a database refresh trait, even without mutation indicators}
        {--json : Output JSON for tooling integrations}';

    /**
     * @var string
     */
    protected $description = 'Find feature tests that might be creating database records without RefreshDatabase or similar traits.';

    public function handle(): int
    {
        $directory = base_path($this->argument('path'));

        if (! is_dir($directory)) {
            $this->components->error(sprintf('The path [%s] is not a directory.', $directory));

            return self::FAILURE;
        }

        $filesystem = new Filesystem;
        $finder = (new Finder)
            ->files()
            ->in($directory)
            ->name('*.php');

        $flagged = [];
        $missing = [];

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $contents = $filesystem->get($file->getRealPath());

            if ($this->usesRefreshTrait($contents)) {
                continue;
            }

            $indicators = $this->matchMutationIndicators($contents);
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());

            if ($indicators !== []) {
                $flagged[] = [
                    'file'       => $relativePath,
                    'indicators' => $indicators,
                ];

                continue;
            }

            if ($this->option('include-all')) {
                $missing[] = $relativePath;
            }
        }

        if ($this->option('json')) {
            $this->line((string) json_encode([
                'flagged' => $flagged,
                'missing' => $missing,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            if ($flagged === []) {
                $this->components->info('No potential offenders were found.');
            } else {
                $this->components->warn('Potential database-mutating tests without refresh traits:');

                foreach ($flagged as $result) {
                    $this->line(sprintf(' - %s (%s)', $result['file'], implode(', ', $result['indicators'])));
                }
            }

            if ($missing !== []) {
                $this->newLine();
                $this->components->warn('Files missing a refresh trait (no mutation indicators found):');

                foreach ($missing as $file) {
                    $this->line(sprintf(' - %s', $file));
                }
            }
        }

        if ($flagged !== [] && $this->option('fail-on-find')) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function usesRefreshTrait(string $contents): bool
    {
        foreach (self::DATABASE_REFRESH_TRAITS as $trait) {
            if (str_contains($contents, $trait)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function matchMutationIndicators(string $contents): array
    {
        $indicators = [];

        foreach (self::MUTATION_PATTERNS as $label => $pattern) {
            if (preg_match($pattern, $contents) === 1) {
                $indicators[] = $label;
            }
        }

        return $indicators;
    }
}
