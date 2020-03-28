<?php namespace KitLoong\MigrationsGenerator;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Xethron\MigrationsGenerator\MigrateGenerateCommand;

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

        $this->app->singleton(
            'migration.generate',
            function (Application $app) {
                return new MigrateGenerateCommand(
                    $app->make('Way\Generators\Generator'),
                    $app->make('Way\Generators\Compilers\TemplateCompiler'),
                    $app->make('migration.repository')
                );
            }
        );

        $this->commands('migration.generate');

        // Bind the Repository Interface to $app['migrations.repository']
        $this->app->bind('Illuminate\Database\Migrations\MigrationRepositoryInterface', function ($app) {
            return $app['migration.repository'];
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

    /**
     * Register the config paths
     */
    protected function registerConfig()
    {
        $packageConfigFile = __DIR__.'/../../config/config.php';
        $this->app['config']->set('generators.config', $this->app['files']->getRequire($packageConfigFile));
    }
}
