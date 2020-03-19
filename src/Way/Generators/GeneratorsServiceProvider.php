<?php namespace Way\Generators;

use Illuminate\Support\ServiceProvider;

class GeneratorsServiceProvider extends ServiceProvider
{
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
        $this->registerConfig();
    }

    /**
     * Register the config paths
     */
    public function registerConfig()
    {
        $userConfigFile = $this->app->configPath().'/generators.config.php';
        $packageConfigFile = __DIR__.'/../../config/config.php';
        $config = $this->app['files']->getRequire($packageConfigFile);

        if (file_exists($userConfigFile)) {
            $userConfig = $this->app['files']->getRequire($userConfigFile);
            $config = array_replace_recursive($config, $userConfig);
        }

        $this->app['config']->set('generators.config', $config);
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
