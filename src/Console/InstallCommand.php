<?php

namespace Savrock\Siop\Console;

use Illuminate\Console\Command;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'siop:install')]
class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siop:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install all of the Siop resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->components->info('Installing Siop resources.');

        collect([
            'Service Provider' => fn () => $this->callSilent('vendor:publish', ['--tag' => 'siop-provider']) == 0,
            'Configuration' => fn () => $this->callSilent('vendor:publish', ['--tag' => 'siop-config']) == 0,
        ])->each(fn ($task, $description) => $this->components->task($description, $task));

        $this->registerSiopServiceProvider();

        $this->components->info('Siop scaffolding installed successfully.');
    }

    /**
     * Register the Siop service provider in the application configuration file.
     *
     * @return void
     */
    protected function registerSiopServiceProvider()
    {
        $namespace = Str::replaceLast('\\', '', $this->laravel->getNamespace());

        if (file_exists($this->laravel->bootstrapPath('providers.php'))) {
            ServiceProvider::addProviderToBootstrapFile("{$namespace}\\Providers\\SiopServiceProvider");
        } else {
            $appConfig = file_get_contents(config_path('app.php'));

            if (Str::contains($appConfig, $namespace.'\\Providers\\SiopServiceProvider::class')) {
                return;
            }

            file_put_contents(config_path('app.php'), str_replace(
                "{$namespace}\\Providers\EventServiceProvider::class,".PHP_EOL,
                "{$namespace}\\Providers\EventServiceProvider::class,".PHP_EOL."        {$namespace}\Providers\SiopServiceProvider::class,".PHP_EOL,
                $appConfig
            ));
        }

        file_put_contents(app_path('Providers/SiopServiceProvider.php'), str_replace(
            "namespace App\Providers;",
            "namespace {$namespace}\Providers;",
            file_get_contents(app_path('Providers/SiopServiceProvider.php'))
        ));
    }
}
