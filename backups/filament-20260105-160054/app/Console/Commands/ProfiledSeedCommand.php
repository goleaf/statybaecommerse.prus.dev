<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Console\Seeds\SeedCommand;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * A profiled replacement for Laravel's db:seed command that understands configuration-driven seed sets.
 */
class ProfiledSeedCommand extends SeedCommand
{
    /**
     * Create a new command instance and wire the configuration repository for profile resolution.
     */
    public function __construct(
        ConnectionResolverInterface $resolver,
        ?Dispatcher $dispatcher,
        private readonly ConfigRepository $config
    ) {
        parent::__construct($resolver, $dispatcher);
    }

    /**
     * Add the --profile option on top of the base db:seed signature.
     */
    protected function configure(): void
    {
        parent::configure();

        $this->addOption(
            'profile',
            null,
            InputOption::VALUE_REQUIRED,
            'Select a seeder profile defined in config/seeds.php.'
        );
    }

    /**
     * Execute the command after resolving and validating the requested profile.
     */
    public function handle(): int
    {
        $profiles = $this->config->get('seeds.profiles', []);

        if (! is_array($profiles) || $profiles === []) {
            // Preserve legacy behaviour if profiles are not configured.
            return parent::handle();
        }

        $requestedProfile = $this->option('profile');
        $defaultProfile = (string) $this->config->get('seeds.default_profile', 'full');
        $profile = is_string($requestedProfile) && $requestedProfile !== ''
            ? $requestedProfile
            : $defaultProfile;

        if (! array_key_exists($profile, $profiles)) {
            // Guide the caller towards a valid profile while returning a non-zero exit code.
            $this->components->error(sprintf(
                'Seeder profile "%s" is not defined. Available profiles: %s.',
                $profile,
                implode(', ', array_keys($profiles))
            ));

            return SymfonyCommand::FAILURE;
        }

        $previousProfile = $this->config->get('seeds.active_profile');

        // Store the active profile so DatabaseSeeder can detect which seed set to run.
        $this->config->set('seeds.active_profile', $profile);
        $this->components->info(sprintf('Using "%s" seeder profile.', $profile));

        try {
            return parent::handle();
        } finally {
            // Restore the previous configuration value to avoid leaking state across commands.
            if ($previousProfile !== null) {
                $this->config->set('seeds.active_profile', $previousProfile);
            } elseif (method_exists($this->config, 'offsetUnset')) {
                $this->config->offsetUnset('seeds.active_profile');
            } else {
                $this->config->set('seeds.active_profile', null);
            }
        }
    }
}
