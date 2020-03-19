<?php namespace Way\Generators;

use Illuminate\Support\ServiceProvider;
use Way\Generators\Commands\ControllerGeneratorCommand;
use Way\Generators\Commands\ModelGeneratorCommand;
use Way\Generators\Commands\ResourceGeneratorCommand;
use Way\Generators\Commands\SeederGeneratorCommand;
use Way\Generators\Commands\PublishTemplatesCommand;
use Way\Generators\Commands\ScaffoldGeneratorCommand;
use Way\Generators\Commands\ViewGeneratorCommand;
use Way\Generators\Commands\PivotGeneratorCommand;

class GeneratorsServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;


    /**
     * Booting
     */
    public function boot()
    {
        // If you need to override the default config, copy config/config.php to /config/generators.config.php and update
        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('generators.config.php'),
        ]);
    }

	/**
	 * Register the commands
	 *
	 * @return void
	 */
	public function register()
	{
        foreach([
            'Model',
            'View',
            'Controller',
            'Migration',
            'Seeder',
            'Pivot',
            'Resource',
            'Scaffold',
            'Publisher'] as $command)
        {
            $this->{"register$command"}();
        }

        $this->registerConfig();
	}

    /**
     * Register the model generator
     */
    protected function registerModel()
    {
        $this->app->singleton('generate.model', function($app)
        {
            $generator = $this->app->make('Way\Generators\Generator');

            return new ModelGeneratorCommand($generator);
        });

        $this->commands('generate.model');
    }

    /**
     * Register the config paths
     */
    public function registerConfig()
    {
        $userConfigFile    = $this->app->configPath().'/generators.config.php';
        $packageConfigFile = __DIR__.'/../../config/config.php';
        $config            = $this->app['files']->getRequire($packageConfigFile);

        if (file_exists($userConfigFile)) {
            $userConfig = $this->app['files']->getRequire($userConfigFile);
            $config     = array_replace_recursive($config, $userConfig);
        }

        $this->app['config']->set('generators.config', $config);
    }

    /**
     * Register the view generator
     */
    protected function registerView()
    {
        $this->app->singleton('generate.view', function($app)
        {
            $generator = $this->app->make('Way\Generators\Generator');

            return new ViewGeneratorCommand($generator);
        });

        $this->commands('generate.view');
    }

    /**
     * Register the controller generator
     */
    protected function registerController()
    {
        $this->app->singleton('generate.controller', function($app)
        {
            $generator = $this->app->make('Way\Generators\Generator');

            return new ControllerGeneratorCommand($generator);
        });

        $this->commands('generate.controller');
    }

    /**
     * Register the migration generator
     */
    protected function registerMigration()
    {
        $this->app->singleton('generate.migration', function($app)
        {
            return $this->app->make('Way\Generators\Commands\MigrationGeneratorCommand');
        });

        $this->commands('generate.migration');
    }

    /**
     * Register the seeder generator
     */
    protected function registerSeeder()
    {
        $this->app->singleton('generate.seeder', function($app)
        {
            $generator = $this->app->make('Way\Generators\Generator');

            return new SeederGeneratorCommand($generator);
        });

        $this->commands('generate.seeder');
    }

    /**
     * Register the pivot generator
     */
    protected function registerPivot()
    {
        $this->app->singleton('generate.pivot', function($app)
        {
            return new PivotGeneratorCommand;
        });

        $this->commands('generate.pivot');
    }

    /**
     * Register the resource generator
     */
    protected function registerResource()
    {
        $this->app->singleton('generate.resource', function($app)
        {
            $generator = $this->app->make('Way\Generators\Generator');

            return new ResourceGeneratorCommand($generator);
        });

        $this->commands('generate.resource');
    }

    /**
     * register command for publish templates
     */
    public function registerpublisher()
    {
        $this->app->singleton('generate.publish-templates', function($app)
        {
            return new publishtemplatescommand;
        });

        $this->commands('generate.publish-templates');
    }

    /**
     * register scaffold command
     */
    public function registerScaffold()
    {
        $this->app->singleton('generate.scaffold', function($app)
        {
            return new ScaffoldGeneratorCommand;
        });

        $this->commands('generate.scaffold');
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

}
