<?php

declare(strict_types=1);

namespace App\Console\Commands;

use function array_filter;
use function array_map;
use function array_values;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Illuminate\Database\Console\Seeds\SeedCommand;
use Illuminate\Database\Eloquent\Model;

use function in_array;
use function is_array;
use function is_string;
use function strtolower;

use Symfony\Component\Console\Input\InputOption;
use Throwable;

use function trim;

final class ProfiledSeedCommand extends SeedCommand
{
    public function __construct(
        Resolver $resolver,
        ?Dispatcher $dispatcher = null,
        ?ConfigRepository $configRepository = null,
    ) {
        parent::__construct($resolver);
    }

    public function handle(): int
    {
        if ($this->isProhibited() || ! $this->confirmToProceed()) {
            return Command::FAILURE;
        }

        $this->applySeedProfile();

        $this->components->info('Seeding database.');

        $previousConnection = $this->resolver->getDefaultConnection();
        $this->resolver->setDefaultConnection($this->getDatabase());

        try {
            if ((bool) $this->option('clear')) {
                $clearedTables = $this->clearSeedableTables();

                $this->components->info('Cleared ' . $clearedTables . ' table(s) before seeding.');
            }

            Model::unguarded(function (): void {
                $this->getSeeder()->__invoke();
            });
        } finally {
            if ($previousConnection) {
                $this->resolver->setDefaultConnection($previousConnection);
            }
        }

        return Command::SUCCESS;
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    protected function getOptions(): array
    {
        return array_merge(parent::getOptions(), [
            ['profile', null, InputOption::VALUE_OPTIONAL, 'Seed profile from config/seeds.php (for DatabaseSeeder runs)'],
            ['clear', null, InputOption::VALUE_NONE, 'Clear application tables before seeding'],
        ]);
    }

    private function applySeedProfile(): void
    {
        $profile = $this->option('profile');

        if (! is_string($profile) || trim($profile) === '') {
            return;
        }

        $this->laravel['config']->set('seeds.active_profile', trim($profile));
    }

    private function clearSeedableTables(): int
    {
        $connection = $this->resolver->connection($this->getDatabase());
        $schema = $connection->getSchemaBuilder();
        $driver = strtolower((string) $connection->getDriverName());

        $rawExcludedTables = $this->laravel['config']->get('seeds.truncate_excluded', [
            'migrations',
            'failed_jobs',
            'jobs',
            'job_batches',
            'cache',
            'cache_locks',
            'sessions',
            'personal_access_tokens',
        ]);

        $excludedTables = is_array($rawExcludedTables)
            ? array_values(array_filter(array_map(
                static fn (mixed $table): ?string => is_string($table) ? trim($table) : null,
                $rawExcludedTables
            )))
            : [];

        $tables = $schema->getTableListing();
        $clearedTables = 0;

        $schema->disableForeignKeyConstraints();

        try {
            foreach ($tables as $table) {
                if (! is_string($table) || $table === '' || in_array($table, $excludedTables, true)) {
                    continue;
                }

                if ($driver === 'sqlite') {
                    $connection->table($table)->delete();

                    try {
                        $connection->delete('DELETE FROM sqlite_sequence WHERE name = ?', [$table]);
                    } catch (Throwable) {
                        // Ignore sequence reset failures for tables without auto-increment entries.
                    }
                } else {
                    $connection->table($table)->truncate();
                }

                $clearedTables++;
            }
        } finally {
            $schema->enableForeignKeyConstraints();
        }

        return $clearedTables;
    }
}
