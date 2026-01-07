<?php

namespace LaraZeus\Bolt\Commands;

use Illuminate\Console\Command;
use LaraZeus\Bolt\Concerns\CanManipulateFiles;

class ZeusFieldCommand extends Command
{
    use CanManipulateFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:zeus-field {plugin : filament FQN plugin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create custom field for zeus bolt';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $filamentPluginFullNamespace = $this->argument('plugin');
        $fieldClassName = str($filamentPluginFullNamespace)->explode('\\')->last();

        $path = config('zeus-bolt.collectors.fields.path');
        $namespace = str_replace('\\\\', '\\', trim(config('zeus-bolt.collectors.fields.namespace'), '\\'));

        $this->copyStubToApp('ZeusField', "{$path}/{$fieldClassName}.php", [
            'namespace' => $namespace,
            'plugin' => $filamentPluginFullNamespace,
            'class' => $fieldClassName,
        ]);

        $this->info('zeus field created successfully!');
    }
}
