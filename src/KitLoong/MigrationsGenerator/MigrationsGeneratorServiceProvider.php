<?php namespace KitLoong\MigrationsGenerator;

use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use KitLoong\MigrationsGenerator\Generators\Decorator;
use Way\Generators\Compilers\Compiler;
use Way\Generators\Compilers\TemplateCompiler;
use KitLoong\MigrationsGenerator\Generators\SchemaGenerator;
use Way\Generators\Generator;
use Xethron\MigrationsGenerator\Syntax\AddForeignKeysToTable;
use Xethron\MigrationsGenerator\Syntax\AddToTable;
use Xethron\MigrationsGenerator\Syntax\RemoveForeignKeysFromTable;

class MigrationsGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    private const COMMAND = 'command.migration.generate';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();

        $this->app->singleton(
            self::COMMAND,
            function (Container $app) {
                return new MigrateGenerateCommand(
                    $app->make(Generator::class),
                    $app->make(SchemaGenerator::class),
                    $app->make('migration.repository'),
                    $app->make(Decorator::class)
                );
            }
        );

        $this->commands(self::COMMAND);

        // Bind the Repository Interface to $app['migrations.repository']
        $this->app->bind('Illuminate\Database\Migrations\MigrationRepositoryInterface', function ($app) {
            return $app['migration.repository'];
        });

        $this->app->bind(AddForeignKeysToTable::class, AddForeignKeysToTable::class);
        $this->app->bind(AddToTable::class, AddToTable::class);
        $this->app->bind(RemoveForeignKeysFromTable::class, RemoveForeignKeysFromTable::class);

        $this->app->singleton(Compiler::class, TemplateCompiler::class);

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
        //
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
