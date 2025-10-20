<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Database\Console\Seeds\SeedCommand;
use Symfony\Component\Console\Input\InputOption;

final class ProfiledSeedCommand extends SeedCommand
{
    public function handle(): int
    {
        $profile = $this->input->getOption('profile');

        if (\is_string($profile) && $profile !== '') {
            $normalized = strtolower($profile);

            $this->laravel['config']->set('seeds.runtime_profile', $normalized);
        }

        return parent::handle();
    }

    protected function getOptions(): array
    {
        $options = parent::getOptions();

        $options[] = ['profile', null, InputOption::VALUE_REQUIRED, 'Seed profile to use (minimal or full).'];

        return $options;
    }
}
