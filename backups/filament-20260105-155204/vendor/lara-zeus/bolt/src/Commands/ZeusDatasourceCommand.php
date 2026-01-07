<?php

namespace LaraZeus\Bolt\Commands;

use Illuminate\Console\Command;
use LaraZeus\Bolt\Concerns\CanManipulateFiles;

class ZeusDatasourceCommand extends Command
{
    use CanManipulateFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:zeus-datasource {name : Datasource Name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create custom Datasource for zeus bolt';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $filamentPluginFullNamespace = $this->argument('name');

        $path = config('zeus-bolt.collectors.dataSources.path');
        $namespace = str_replace('\\\\', '\\', trim(config('zeus-bolt.collectors.dataSources.namespace'), '\\'));

        $this->copyStubToApp('ZeusDataSources', "{$path}/{$filamentPluginFullNamespace}.php", [
            'namespace' => $namespace,
            'class' => $filamentPluginFullNamespace,
        ]);

        $this->info('zeus datasource created successfully!');
    }
}
