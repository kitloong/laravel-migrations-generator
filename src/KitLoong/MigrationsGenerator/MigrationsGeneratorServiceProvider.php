<?php namespace KitLoong\MigrationsGenerator;

use Illuminate\Support\ServiceProvider;

class MigrationsGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();

        // Bind the Repository Interface to $app['migrations.repository']
        $this->app->bind('Illuminate\Database\Migrations\MigrationRepositoryInterface', function ($app) {
            return $app['migration.repository'];
        });

        $this->app->singleton(MigrationsGeneratorSetting::class, function () {
            return new MigrationsGeneratorSetting();
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MigrateGenerateCommand::class,
            ]);
        }
    }

    /**
     * Register the config paths
     */
    protected function registerConfig()
    {
        $packageConfigFile = __DIR__.'/../../config/config.php';
        $this->app->make('config')->set(
            'generators.config',
            $this->app->make('files')->getRequire($packageConfigFile)
        );
    }
}
