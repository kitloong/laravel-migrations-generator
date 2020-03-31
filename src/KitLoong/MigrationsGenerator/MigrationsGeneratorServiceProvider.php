<?php namespace KitLoong\MigrationsGenerator;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Way\Generators\Compilers\Compiler;
use Way\Generators\Compilers\TemplateCompiler;
use KitLoong\MigrationsGenerator\Generators\SchemaGenerator;
use Xethron\MigrationsGenerator\MigrateGenerateCommand;
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
            function (Application $app) {
                return new MigrateGenerateCommand(
                    $app->make('Way\Generators\Generator'),
                    $app->make(SchemaGenerator::class),
                    $app->make('migration.repository')
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

        $this->app->singleton('connection', function () {
            $connection = new Connection();
            $connection->setConnection($this->app->get('config')->get('database.default'));
            return $connection;
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
        $this->app->get('config')->set(
            'generators.config',
            $this->app->get('files')->getRequire($packageConfigFile)
        );
    }
}
